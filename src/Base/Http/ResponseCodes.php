<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Http;

class ResponseCodes
{
    final public const HTTP_ACCEPTED                        = 202;
    final public const HTTP_ALREADY_REPORTED                = 208;
    final public const HTTP_BAD_REQUEST                     = 400;
    final public const HTTP_CONFLICT                        = 409;
    final public const HTTP_CONTINUE                        = 100;
    final public const HTTP_CREATED                         = 201;
    final public const HTTP_EARLY_HINTS                     = 103;
    final public const HTTP_EXPECTATION_FAILED              = 417;
    final public const HTTP_FAILED_DEPENDENCY               = 424;
    final public const HTTP_FORBIDDEN                       = 403;
    final public const HTTP_FOUND                           = 302;
    final public const HTTP_GONE                            = 410;
    final public const HTTP_IM_USED                         = 226;
    final public const HTTP_I_AM_A_TEAPOT                   = 418;
    final public const HTTP_LENGTH_REQUIRED                 = 411;
    final public const HTTP_LOCKED                          = 423;
    final public const HTTP_METHOD_NOT_ALLOWED              = 405;
    final public const HTTP_MISDIRECTED_REQUEST             = 421;
    final public const HTTP_MOVED_PERMANENTLY               = 301;
    final public const HTTP_MULTIPLE_CHOICES                = 300;
    final public const HTTP_MULTI_STATUS                    = 207;
    final public const HTTP_NON_AUTHORITATIVE_INFORMATION   = 203;
    final public const HTTP_NOT_ACCEPTABLE                  = 406;
    final public const HTTP_NOT_FOUND                       = 404;
    final public const HTTP_NOT_MODIFIED                    = 304;
    final public const HTTP_NO_CONTENT                      = 204;
    final public const HTTP_OK                              = 200;
    final public const HTTP_PARTIAL_CONTENT                 = 206;
    final public const HTTP_PAYMENT_REQUIRED                = 402;
    final public const HTTP_PERMANENTLY_REDIRECT            = 308;
    final public const HTTP_PRECONDITION_FAILED             = 412;
    final public const HTTP_PROCESSING                      = 102;
    final public const HTTP_PROXY_AUTHENTICATION_REQUIRED   = 407;
    final public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    final public const HTTP_REQUEST_ENTITY_TOO_LARGE        = 413;
    final public const HTTP_REQUEST_TIMEOUT                 = 408;
    final public const HTTP_REQUEST_URI_TOO_LONG            = 414;
    final public const HTTP_RESERVED                        = 306;
    final public const HTTP_RESET_CONTENT                   = 205;
    final public const HTTP_SEE_OTHER                       = 303;
    final public const HTTP_SWITCHING_PROTOCOLS             = 101;
    final public const HTTP_TEMPORARY_REDIRECT              = 307;
    final public const HTTP_UNAUTHORIZED                    = 401;
    final public const HTTP_UNPROCESSABLE_ENTITY            = 422;
    final public const HTTP_UNSUPPORTED_MEDIA_TYPE          = 415;
    final public const HTTP_USE_PROXY                       = 305;
}
