# SecureHeaders
A PHP class aiming to make the use of browser security features more accessible, while allowing developers to safely experiment with these features to ensure they are configured correctly. Security headers also can be configured, or built using SecureHeaders. 

The project aims help increase the overall security of an application in-which it runs within. 

Sometimes this is most appropriately applied through feedback. SecureHeaders will issue warnings (`level E_USER_WARNING`) and notices (`level E_USER_NOTICE`) at runtime when it notices something is wrong. 

In some cases, correcting insecure behaviour is best done pro-actively. SecureHeaders will modify or add headers (where safe to do so). (This can of course be granularly controlled, or outright disabled). This includes adding security flags to cookies with certain keywords in their names in an effort to protect session data. And also by adding missing security headers to automatically enable client browser security features.

## Development Notice
This project is currently under initial development, so there is the potential for non-backwards compatible changes etc.. That said, bug reports are still welcome from anyone who wants to test it out.

## Features
* Add/remove and manage headers easily
* Build a Content Security Policy, or combine multiple together
* Correct cookie flags on already set cookies to add httpOnly and secure flags, (if the cookies appear to be session related)
* Safe mode prevents accidential self-DOS when using HSTS, or HPKP
* Receive warnings about missing security headers (`level E_USER_WARNING`)

## Usage
*(section nowhere close to complete)*

e.g. the following will combine `$baseCSP` with `$csp` to create an overall Content-Security-Policy.
```php
import('SecureHeaders.php');

$headers = new SecureHeaders();

$baseCSP = array(
  "default-src" => ["'self'"]
);
$headers->csp($baseCSP);

$csp = array(
  "frame-src" => ["https://www.example.com/"],
  "style-src" => ["'nonce-$style_nonce'"],
  "script-src" => ["'nonce-$script_nonce'"]
);
$headers->csp($csp);
```

The `SecureHeaders` class can also be extended, so that custom settings can be applied on all instances of the extension.
e.g. `$baseCSP` on all pages.

```php
class CustomSecureHeaders extends SecureHeaders{
    public function __construct()
    {
      $this->csp($this->base);
    }
    private $base = array(
      "default-src" => ["'self'"],
      "style-src" => [
        "'self'",
        "https://fonts.googleapis.com/",
        "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/"
      ]
    );
}
```

```php
import('SecureHeaders.php');
import('CustomSecureHeaders.php');

$headers = new CustomSecureHeaders();

$pageSpecificCSP = array(
  "frame-src" => ["https://www.example.com/"],
  "style-src" => ["'nonce-$style_nonce'"],
  "script-src" => ["'nonce-$script_nonce'"]
);
$headers->csp($pageSpecificCSP);
```

etc...

This readme is incomplete, please refer to the source, or the (non-exhaustive) example file `CustomSecureHeaders.php` for full usage.

