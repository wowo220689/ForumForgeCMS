param([string]$BaseUrl = 'http://127.0.0.1:8133')
$ErrorActionPreference = 'Stop'
$checks = 0
function Assert-True($value, $message) {
    if (-not $value) { throw "FAIL: $message" }
    $script:checks++
}
function Field($html, $name) {
    $pattern = 'name="' + [regex]::Escape($name) + '" value="([^"]*)"'
    return [System.Net.WebUtility]::HtmlDecode([regex]::Match($html, $pattern).Groups[1].Value)
}
function Page($path, $session) {
    return (Invoke-WebRequest -UseBasicParsing -Uri "$BaseUrl/$path" -WebSession $session).Content
}
function Post($path, $session, $body) {
    return (Invoke-WebRequest -UseBasicParsing -Uri "$BaseUrl/$path" -WebSession $session -Method Post -Body $body).Content
}
$admin = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$login = Page 'index.php?view=login' $admin
$html = Post 'index.php?view=login' $admin @{ action='login'; csrf_token=(Field $login 'csrf_token'); login='admin'; password='Test-only-password-123' }
Assert-True ($html.Contains('Admin panel')) 'administrator login'
$settings = Page 'index.php?view=admin&section=antispam' $admin
Assert-True ($settings.Contains('Spam protection')) 'antispam page'
$html = Post 'index.php?view=admin&section=antispam' $admin @{ action='update_antispam'; csrf_token=(Field $settings 'csrf_token'); antispam_question_enabled='1'; antispam_domains='spam.example'; antispam_usernames='blockeduser'; antispam_ips='192.0.2.1' }
Assert-True ($html.Contains('spam.example')) 'settings saved'
$visitor = New-Object Microsoft.PowerShell.Commands.WebRequestSession
function Registration($name, $email, $answer = '') {
    $html = Page 'index.php?view=register' $visitor
    $sum = [regex]::Match($html, '(\d) \+ (\d)')
    if ($answer -eq '') { $answer = [string]([int]$sum.Groups[1].Value + [int]$sum.Groups[2].Value) }
    $script:registration = @{ action='register'; csrf_token=(Field $html 'csrf_token'); antispam_token=(Field $html 'antispam_token'); antispam_answer=$answer; username=$name; email=$email; password='Testing-password-123'; accept_terms='1' }
    return Post 'index.php?view=register' $visitor $script:registration
}
$html = Registration 'blockeduser' 'person@safe.org'
Assert-True ($html.Contains('This username is blocked.')) 'username block on POST'
$html = Registration 'domainuser' 'person@sub.spam.example'
Assert-True ($html.Contains('Registration from this email domain is blocked.')) 'subdomain block on POST'
$html = Registration 'wronganswer' 'wrong@safe.org' '99'
Assert-True ($html.Contains('incorrect or has expired')) 'wrong answer on POST'
$html = Registration 'limiteduser' 'limited@safe.org'
Assert-True ($html.Contains('too quickly')) 'existing registration rate limit'
$visitor = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$name = 'http' + [guid]::NewGuid().ToString('N').Substring(0, 8)
$html = Registration $name "$name@safe.org"
Assert-True ($html.Contains('You can now log in.')) 'valid registration'
$html = Post 'index.php?view=register' $visitor $registration
Assert-True ($html.Contains('incorrect or has expired')) 'replay blocked on POST'
$member = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$login = Page 'index.php?view=login' $member
$html = Post 'index.php?view=login' $member @{ action='login'; csrf_token=(Field $login 'csrf_token'); login='tester'; password='Testing-password-123' }
$html = Post 'index.php?view=admin&section=antispam' $member @{ action='update_antispam'; csrf_token=(Field $html 'csrf_token'); antispam_domains='hacked.example' }
Assert-True (-not $html.Contains('id="antispam-domains"')) 'member cannot administer spam settings'
$settings = Page 'index.php?view=admin&section=antispam' $admin
Assert-True ($settings.Contains('spam.example') -and -not $settings.Contains('hacked.example')) 'member POST leaves settings unchanged'
$html = Post 'index.php?view=admin&section=antispam' $admin @{ action='update_antispam'; csrf_token='invalid'; antispam_domains='hacked.example' }
Assert-True ($html.Contains('spam.example') -and -not $html.Contains('hacked.example')) 'CSRF protects settings'
$html = Post 'index.php?view=admin&section=antispam' $admin @{ action='update_antispam'; csrf_token=(Field $settings 'csrf_token'); antispam_question_enabled='1'; antispam_ips='127.0.0.1' }
Assert-True ($html.Contains('current IP address')) 'administrator self-lock protection over HTTP'
Write-Output "PASS: $checks HTTP assertions"
