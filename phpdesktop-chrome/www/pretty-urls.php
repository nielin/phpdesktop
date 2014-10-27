<a href="/index.php">Go back to index</a>
| <a href="<?php echo $_SERVER["REQUEST_URI"];?>">Refresh</a>

<title>Pretty urls</title>
<h1>Pretty urls</h1>

<p>
    Pretty urls (url rewriting) are not yet supported, 
    see Issue 81 in the tracker.
</p>
<p>
    However, urls like "index.php/company/5" are supported after applying
    a fix to mongoose $_SERVER/$_ENV variables. See the
    __fix_mongoose_env_variables() php function further down the page.
</p>
<p>
    Test urls: 
    <ul>
    <li><a href="/pretty-urls.php/company/5">
        /pretty-urls.php/company/5</a>
    <li><a href="/pretty-urls.php/company/5?xyz=1">
        /pretty-urls.php/company/5?xyz=1</a>
    <li><a href="/pretty-urls.php/company\5">
        /pretty-urls.php/company\5</a>
    <li><a href="/pretty-urls.php?xyz=1">
        /pretty-urls.php?xyz=1</a>
    </ul>
</p>

<h2>$_SERVER before fix</h2>

<?php
function print_url_variables()
{
    $url_vars = [];
    foreach ($_SERVER as $k => $v) {
        if (is_array($v)) {
            continue;
        }
        if (strpos($v, "pretty-urls.php") !== false
                || strpos($v, "company") !== false
                || strpos($v, "xyz") !== false) {
            $url_vars[$k] = $v;
        }
    }
    print "<pre style='background:#ddd;'>";
    print_r($url_vars);
    print "</pre>";
}
print_url_variables();
?>

<?php
function __fix_mongoose_env_variables() 
{
    // REQUEST_URI does not contain QUERY_STRING. See Issue 112
    // in the tracker. The condition below will be always executed.
    if (strpos($_SERVER["REQUEST_URI"], "?") === false) {
        // Fix PHP_SELF and SCRIPT_NAME env variables which may be
        // broken for pretty urls like "/index.php/company/5":
        // >> [PHP_SELF] => /index.php/company/index.php/company/5
        $php_self = $_SERVER["PHP_SELF"];
        if (strrpos($php_self, "/") !== 0) {
            // When PHP_SELF contains more than one slash. Remove
            // path info from both PHP_SELF and SCRIPT_NAME.
            $php_self = preg_replace('#^(/+[^/\\\]+)[\s\S]+#', 
                    '\\1', $php_self);
            $_SERVER["PHP_SELF"] = $php_self;
            $_SERVER["SCRIPT_NAME"] = $php_self;
        }
        // Append QUERY_STRING to REQUEST_URI env variable.
        if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
            $_SERVER["REQUEST_URI"] = $_SERVER["REQUEST_URI"]."?".(
                    $_SERVER["QUERY_STRING"]);
        }
    }
    // Fix forward slash in SCRIPT_FILENAME and PATH_TRANSLATED:
    // >> C:\phpdesktop\phpdesktop-chrome\www/pretty-urls.php
    // should become:
    // >> C:\phpdesktop\phpdesktop-chrome\www\pretty-urls.php
    $_SERVER["SCRIPT_FILENAME"] = str_replace("/", "\\", 
            $_SERVER["SCRIPT_FILENAME"]);
    $_SERVER["PATH_TRANSLATED"] = str_replace("/", "\\", 
            $_SERVER["PATH_TRANSLATED"]);
    // Fixes were applied to $_SERVER only. Apply them to $_ENV as well.
    $keys_to_fix = ["REQUEST_URI", "SCRIPT_NAME", "PHP_SELF",
            "SCRIPT_FILENAME", "PATH_TRANSLATED"];
    foreach ($keys_to_fix as $env_key) {
        putenv("$env_key={$_SERVER[$env_key]}");
        $_ENV[$env_key] = $_SERVER[$env_key];
    }
}
__fix_mongoose_env_variables();
?>


<h2>$_SERVER after fix</h2>
The following fixes are applied:
<ul>
    <li>REQUEST_URI does not contain QUERY_STRING
    <li>Pretty urls like "/index.php/company/5" have invalid values
        for SCRIPT_NAME and PHP_SELF keys
    <li>SCRIPT_FILENAME and PATH_TRANSLATED contain forward slash 
        before script name
</ul>
<?php print_url_variables(); ?>


<h2>To fix env variables use the code below</h2>
<p>Put this code at the very beginning of php script.</p>
<pre style="background:#ddd;">
&lt;?php
function __fix_mongoose_env_variables() 
{
    // REQUEST_URI does not contain QUERY_STRING. See Issue 112
    // in the tracker. The condition below will be always executed.
    if (strpos($_SERVER["REQUEST_URI"], "?") === false) {
        // Fix PHP_SELF and SCRIPT_NAME env variables which may be
        // broken for pretty urls like "/index.php/company/5":
        // >> [PHP_SELF] => /index.php/company/index.php/company/5
        $php_self = $_SERVER["PHP_SELF"];
        if (strrpos($php_self, "/") !== 0) {
            // When PHP_SELF contains more than one slash. Remove
            // path info from both PHP_SELF and SCRIPT_NAME.
            $php_self = preg_replace('#^(/+[^/\\\]+)[\s\S]+#', 
                    '\\1', $php_self);
            $_SERVER["PHP_SELF"] = $php_self;
            $_SERVER["SCRIPT_NAME"] = $php_self;
        }
        // Append QUERY_STRING to REQUEST_URI env variable.
        if (isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) {
            $_SERVER["REQUEST_URI"] = $_SERVER["REQUEST_URI"]."?".(
                    $_SERVER["QUERY_STRING"]);
        }
    }
    // Fix forward slash in SCRIPT_FILENAME and PATH_TRANSLATED:
    // >> C:\phpdesktop\phpdesktop-chrome\www/pretty-urls.php
    // should become:
    // >> C:\phpdesktop\phpdesktop-chrome\www\pretty-urls.php
    $_SERVER["SCRIPT_FILENAME"] = str_replace("/", "\\", 
            $_SERVER["SCRIPT_FILENAME"]);
    $_SERVER["PATH_TRANSLATED"] = str_replace("/", "\\", 
            $_SERVER["PATH_TRANSLATED"]);
    // Fixes were applied to $_SERVER only. Apply them to $_ENV as well.
    $keys_to_fix = ["REQUEST_URI", "SCRIPT_NAME", "PHP_SELF",
            "SCRIPT_FILENAME", "PATH_TRANSLATED"];
    foreach ($keys_to_fix as $env_key) {
        putenv("$env_key={$_SERVER[$env_key]}");
        $_ENV[$env_key] = $_SERVER[$env_key];
    }
}
__fix_mongoose_env_variables();
?&gt;
</pre>