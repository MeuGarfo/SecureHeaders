<?php

namespace Aidantwoods\SecureHeaders\Operations;

use Aidantwoods\SecureHeaders\ExposesErrors;
use Aidantwoods\SecureHeaders\Header;
use Aidantwoods\SecureHeaders\HeaderBag;
use Aidantwoods\SecureHeaders\Operation;

class InjectStrictDynamic extends OperationWithErrors implements Operation, ExposesErrors
{
    private $allowedCSPHashAlgs;

    /**
     * Create an Operation to inject `'strict-dynamic'` into an appropriate
     * CSP directive, $allowedCSPHashAlgs supplies a list of allowed CSP
     * hashing algorithms.
     *
     * @param array $hstsConfig
     */
    public function __construct(array $allowedCSPHashAlgs)
    {
        $this->allowedCSPHashAlgs = $allowedCSPHashAlgs;
    }

    /**
     * Transform the given set of headers
     *
     * @param HeaderBag $headers
     * @return void
     */
    public function modify(HeaderBag &$headers)
    {
        $CSPHeaders = $headers->getByName('content-security-policy');

        if (isset($CSPHeaders[0]))
        {
            $header = $CSPHeaders[0];

            $directive = $this->canInjectStrictDynamic($header);

            if (is_string($directive))
            {
                $header->setAttribute($directive, "'strict-dynamic'");
            }
            elseif ($directive !== -1)
            {
                $this->addError(
                    "<b>Strict-Mode</b> is enabled, but <b>'strict-dynamic'</b>
                        could not be added to the Content-Security-Policy
                        because no hash or nonce was used.",
                    E_USER_WARNING
                );
            }
        }
    }

    /**
     * Determine which directive `'strict-dynamic'` may be injected into, if
     * any.
     * If Safe-Mode conflicts, `-1` will be returned.
     * If `'strict-dynamic'` cannot be injected, `false` will be returned.
     *
     * @return string|int|bool
     */
    private function canInjectStrictDynamic(Header $header)
    {
        # check if a relevant directive exists
        if (
            $header->hasAttribute($directive = 'script-src')
            or $header->hasAttribute($directive = 'default-src')
        ) {
            if (
                preg_match(
                    "/(?:^|\s)(?:'strict-dynamic'|'none')(?:$|\s)/i",
                    $header->getAttributeValue($directive)
                )
            ) {
                return -1;
            }

            $nonceOrHashRe = implode(
                '|',
                array_merge(
                    ['nonce'],
                    $this->allowedCSPHashAlgs
                )
            );

            # if the directive contains a nonce or hash, return the directive
            # that strict-dynamic should be injected into
            $containsNonceOrHash = preg_match(
                "/(?:^|\s)'(?:$nonceOrHashRe)-/i",
                $header->getAttributeValue($directive)
            );

            if ($containsNonceOrHash)
            {
                return $directive;
            }
        }

        return false;
    }
}
