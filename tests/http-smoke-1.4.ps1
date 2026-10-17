param([Parameter(Mandatory=$true)][string]$FixtureDirectory, [string]$BaseUrl = 'http://127.0.0.1:8144')
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'http-smoke.ps1') -BaseUrl $BaseUrl
$meta = Get-Content -Raw -LiteralPath (Join-Path $FixtureDirectory 'fixture.json') | ConvertFrom-Json
function Login-As($username) {
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $login = Page 'index.php?view=login' $session
    $null = Post 'index.php?view=login' $session @{action='login';csrf_token=(Field $login 'csrf_token');login=$username;password='Testing-password-123'}
    return $session
}
$visitor = New-Object Microsoft.PowerShell.Commands.WebRequestSession
foreach ($mode in @('members','staff','hidden')) {
    $response = Invoke-WebRequest -UseBasicParsing -SkipHttpErrorCheck -Uri "$BaseUrl/index.php?view=topic&id=$($meta.cases.$mode.topic)" -WebSession $visitor
    Assert-True ($response.StatusCode -eq 404) "guest cannot open $mode topic"
    Assert-True (-not $response.Content.Contains("AccessMarker-content-$mode")) 'forbidden body absent'
}
$html = Page 'index.php?view=search&q=AccessMarker' $visitor
Assert-True (-not $html.Contains('AccessMarker-hidden') -and -not $html.Contains('AccessMarker-staff') -and -not $html.Contains('AccessMarker-members')) 'search filters guest results'
$memberSession = Login-As 'member14'
$account = Page 'index.php?view=account' $memberSession
Assert-True ($account.Contains('name="about_me"') -and $account.Contains('name="signature"')) 'profile editor available'
$html = Post 'index.php?view=account' $memberSession @{action='update_profile';csrf_token=(Field $account 'csrf_token');user_id='1';about_me='Profile HTTP test';location='Köln / Łódź';website='https://example.com/';signature='<script>test14</script>'}
Assert-True ($html.Contains('Your profile has been updated.')) 'profile save'
$html = Page "index.php?view=user&id=$($meta.member)" $visitor
Assert-True ($html.Contains('Profile HTTP test') -and $html.Contains('Köln / Łódź')) 'public profile round trip'
Assert-True ($html.Contains('&lt;script&gt;test14&lt;/script&gt;') -and -not $html.Contains('<script>test14</script>')) 'profile HTML escaped'
$html = Page 'index.php?view=user&id=1' $visitor
Assert-True (-not $html.Contains('Profile HTTP test')) 'posted user ID cannot edit another profile'
$html = Page "index.php?view=topic&id=$($meta.owned_topic)" $visitor
Assert-True ($html.Contains('&lt;script&gt;test14&lt;/script&gt;')) 'signature displayed safely below posts'
$readonly = "index.php?view=category&id=$($meta.cases.readonly.category)"
$html = Page $readonly $memberSession
Assert-True (-not $html.Contains('value="create_topic"')) 'read-only forum hides composer'
$html = Post $readonly $memberSession @{action='create_post';csrf_token=(Field $account 'csrf_token');topic_id=$meta.cases.readonly.topic;body='Forbidden HTTP reply'}
Assert-True ($html.Contains('You do not have permission to write in this forum.')) 'forged read-only reply denied'
$html = Page "index.php?view=topic&id=$($meta.cases.readonly.topic)" $memberSession
Assert-True (-not $html.Contains('Forbidden HTTP reply') -and -not $html.Contains('value="create_post"')) 'read-only reply not inserted'
$html = Page "index.php?view=topic&id=$($meta.cases.public.topic)&quote_post=$($meta.cases.hidden.post)" $memberSession
Assert-True (-not $html.Contains('AccessMarker-content-hidden')) 'cross-topic quote cannot expose hidden content'
$notifications = Page 'index.php?view=notifications' $memberSession
Assert-True ($notifications.Contains('Mark all as read') -and $notifications.Contains('value="open_notification"')) 'notification inbox renders'
$noticeId = Field $notifications 'notification_id'
$otherSession = Login-As 'other14'
$otherPage = Page 'index.php?view=notifications' $otherSession
$html = Post 'index.php?view=notifications' $otherSession @{action='open_notification';csrf_token=(Field $otherPage 'csrf_token');notification_id=$noticeId}
Assert-True ($html.Contains('This notification is no longer available.')) 'notification ownership enforced'
$html = Post 'index.php?view=notifications' $memberSession @{action='open_notification';csrf_token=(Field $notifications 'csrf_token');notification_id=$noticeId}
Assert-True ($html.Contains('Notification test')) 'notification opens correct thread'
$html = Post 'index.php?view=notifications' $memberSession @{action='read_notifications';csrf_token=(Field $notifications 'csrf_token')}
Assert-True (-not $html.Contains('class="forum-row forum-notification-unread"')) 'mark-all-read HTTP action'
$manage = Page 'index.php?view=admin&section=create-category' $admin
Assert-True ($manage.Contains('name="visibility"') -and $manage.Contains('name="read_only"')) 'admin permission controls'
$name = 'HTTP staff ' + [guid]::NewGuid().ToString('N').Substring(0,8)
$null = Post 'index.php?view=admin&section=create-category' $admin @{action='create_category';csrf_token=(Field $manage 'csrf_token');name=$name;description='Staff forum from HTTP test';section_id='1';visibility='staff';read_only='1'}
$html = Page 'index.php' $visitor
Assert-True (-not $html.Contains($name)) 'new staff forum hidden from guest'
$modSession = Login-As 'mod14'
$html = Page 'index.php' $modSession
Assert-True ($html.Contains($name)) 'new staff forum visible to moderator'
for ($i=0; $i -lt 8; $i++) {
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $login = Page 'index.php?view=login' $session
    $html = Post 'index.php?view=login' $session @{action='login';csrf_token=(Field $login 'csrf_token');login='other14';password='wrong-password'}
    Assert-True ($html.Contains('Check your login and password.')) 'failed login in a new session'
}
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$login = Page 'index.php?view=login' $session
$html = Post 'index.php?view=login' $session @{action='login';csrf_token=(Field $login 'csrf_token');login='other14@safe.org';password='Testing-password-123'}
Assert-True ($html.Contains('Too many login attempts.')) 'persistent lock survives new cookies and email alias'
Write-Output "PASS: $checks combined HTTP assertions"
