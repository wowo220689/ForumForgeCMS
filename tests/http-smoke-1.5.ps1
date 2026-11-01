param([Parameter(Mandatory=$true)][string]$FixtureDirectory, [string]$BaseUrl = 'http://127.0.0.1:8155')
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'http-smoke-1.4.ps1') -FixtureDirectory $FixtureDirectory -BaseUrl $BaseUrl
$manage = Page 'index.php?view=admin&section=seo' $admin
$csrf = Field $manage 'csrf_token'
$html = Post 'index.php?view=admin&section=seo' $admin @{action='update_operations';operations_section='seo';site_url='https://example.com/forge';csrf_token=$csrf}
Assert-True ($html.Contains('https://example.com/forge')) 'SEO URL saved'
$html = Page "index.php?view=topic&id=$($meta.cases.public.topic)&quote_post=1&token=PrivateToken" $visitor
Assert-True ($html.Contains('rel="canonical" href="https://example.com/forge/index.php?view=topic&amp;id=') -and -not $html.Contains('og:url" content="http://127.0.0.1')) 'canonical uses configured URL'
Assert-True ($html.Contains('property="og:description" content="AccessMarker-content-public"')) 'public topic excerpt in Open Graph'
$html = Page "index.php?view=topic&id=$($meta.cases.hidden.topic)" $admin
Assert-True ($html.Contains('content="noindex,nofollow"') -and -not $html.Contains('property="og:description" content="AccessMarker')) 'private topic excluded from indexing and sharing excerpt'
$html = Page 'index.php?view=search&q=AccessMarker' $visitor
Assert-True ($html.Contains('content="noindex,nofollow"') -and -not $html.Contains('rel="canonical"')) 'search not indexed'
foreach ($section in @('hosting','maintenance','seo','statistics')) {
    $html = Page "index.php?view=admin&section=$section" $admin
    Assert-True ($html.Contains('ForumForgeCMS 1.5')) "new admin screen: $section"
}
$html = Page 'index.php?view=admin&section=statistics&from=2026-02-30&to=2026-03-01' $admin
Assert-True ($html.Contains('Choose valid dates')) 'invalid statistics dates handled'
$probe = Post 'index.php?view=admin&section=hosting' $admin @{action='hosting_probe';csrf_token=$csrf} | ConvertFrom-Json
Assert-True ($probe.url -match '^forum-data/hosting-check-[a-f0-9]{32}\.txt$') 'HTTP probe generated safely'
$result = Invoke-WebRequest -Uri "$BaseUrl/$($probe.url)" -UseBasicParsing -SkipHttpErrorCheck
Assert-True ($result.StatusCode -eq 200 -and $result.Content -match 'temporary access check') 'built-in server deliberately exposes harmless probe (no .htaccess support)'
$null = Post 'index.php?view=admin&section=hosting' $admin @{action='hosting_probe_cleanup';csrf_token=$csrf}
$result = Invoke-WebRequest -Uri "$BaseUrl/$($probe.url)" -UseBasicParsing -SkipHttpErrorCheck
Assert-True ($result.StatusCode -eq 404) 'HTTP probe cleaned'
$html = Post 'index.php?view=admin&section=maintenance' $memberSession @{action='update_operations';operations_section='maintenance';maintenance_enabled='1';csrf_token=(Field $account 'csrf_token')}
Assert-True (-not $html.Contains('name="maintenance_message"')) 'member cannot enable maintenance'
$html = Post 'index.php?view=admin&section=maintenance' $admin @{action='update_operations';operations_section='maintenance';maintenance_enabled='1';maintenance_message='Test maintenance <script>not executed</script>';csrf_token=$csrf}
Assert-True ($html.Contains('name="maintenance_enabled" value="1" checked')) 'maintenance enabled by administrator'
foreach ($path in @('index.php','index.php?view=search&q=AccessMarker','avatar.php?id=1','logo.php','index.php?view=topic&id=1')) {
    $response = Invoke-WebRequest -UseBasicParsing -SkipHttpErrorCheck -Uri "$BaseUrl/$path" -WebSession $visitor
    Assert-True ($response.StatusCode -eq 503 -and $response.Headers['Retry-After'] -contains '3600') "maintenance blocks $path"
}
$response = Invoke-WebRequest -UseBasicParsing -SkipHttpErrorCheck -Uri "$BaseUrl/index.php?view=login" -WebSession $memberSession -Method Post -Body @{action='create_post';topic_id='1';body='BypassMaintenanceMarker';csrf_token=(Field $account 'csrf_token')}
Assert-True ($response.StatusCode -eq 503) 'maintenance blocks forged POST through login route'
$html = Page 'index.php?view=login' $visitor
Assert-True ($html.Contains('name="login"')) 'maintenance administrator login available'
$html = Page 'index.php?view=admin&section=maintenance' $admin
Assert-True ($html.Contains('name="maintenance_message"')) 'administrator retains panel access'
$null = Post 'index.php?view=admin&section=maintenance' $admin @{action='update_operations';operations_section='maintenance';csrf_token=$csrf}
$deleteSession = Login-As 'http15'
$deletePage = Page 'index.php?view=account' $deleteSession
Assert-True ($deletePage.Contains('value="delete_own_account"') -and $deletePage.Contains('name="confirm_deletion"')) 'account deletion form and consent'
$token = Field $deletePage 'csrf_token'
$html = Post 'index.php?view=account' $deleteSession @{action='delete_own_account';csrf_token=$token;current_password='wrong';confirm_deletion='1'}
Assert-True ($html.Contains('current password is incorrect')) 'deletion password checked'
$html = Post 'index.php?view=account' $deleteSession @{action='delete_own_account';csrf_token=$token;current_password='Testing-password-123'}
Assert-True ($html.Contains('Confirm that you understand')) 'deletion explicit consent checked'
$html = Post 'index.php?view=account' $deleteSession @{action='delete_own_account';csrf_token='invalid';current_password='Testing-password-123';confirm_deletion='1'}
Assert-True (-not $html.Contains('Your account has been deleted')) 'deletion CSRF protection'
$html = Post 'index.php?view=account' $deleteSession @{action='delete_own_account';csrf_token=$token;user_id='1';current_password='Testing-password-123';confirm_deletion='1'}
Assert-True ($html.Contains('Your account has been deleted')) 'own account deletion ignores forged target ID'
$html = Page 'index.php?view=admin' $admin
Assert-True ($html.Contains('Administrator panel')) 'administrator account unaffected'
$backupPath = Join-Path $FixtureDirectory 'http-backup-1.5.zip'
$null = Invoke-WebRequest -UseBasicParsing -Uri "$BaseUrl/index.php?view=admin&section=backup" -WebSession $admin -Method Post -Body @{action='download_backup';csrf_token=$csrf} -OutFile $backupPath
$archive = [System.IO.Compression.ZipFile]::OpenRead($backupPath)
try {
    Assert-True ($archive.GetEntry('forum-data/forum.sqlite').Length -gt 0) 'backup includes SQLite database after anonymization'
    $reader = [System.IO.StreamReader]::new($archive.GetEntry('backup-info.txt').Open())
    try { $info = $reader.ReadToEnd() } finally { $reader.Dispose() }
    Assert-True ($info.Contains('Version: 1.5')) 'backup identifies new version'
    Assert-True (-not ($archive.Entries.FullName -match 'hosting-check-|admin-initial-password')) 'backup excludes probe and initial password'
} finally { $archive.Dispose() }
$html = Page 'index.php?view=account' $deleteSession
Assert-True (-not $html.Contains('value="delete_own_account"')) 'deleted session cannot open account'
$null = Post 'index.php?view=admin&section=seo' $admin @{action='update_operations';operations_section='seo';site_url='';csrf_token=$csrf}
Write-Output "PASS: $checks combined HTTP assertions (1.5)"
