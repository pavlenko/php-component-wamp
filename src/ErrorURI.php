<?php

namespace PE\Component\WAMP;

final class ErrorURI
{
    const _UNKNOWN                       = 'wamp.error.unknown';
    const _INVALID_URI                   = 'wamp.error.invalid_uri';
    const _NO_SUCH_PROCEDURE             = 'wamp.error.no_such_procedure';
    const _NO_SUCH_CALL                  = 'wamp.error.no_such_call';
    const _NO_SUCH_REGISTRATION          = 'wamp.error.no_such_registration';
    const _NO_SUCH_SUBSCRIPTION          = 'wamp.error.no_such_subscription';
    const _NO_SUCH_REALM                 = 'wamp.error.no_such_realm';
    const _NO_SUCH_ROLE                  = 'wamp.error.no_such_role';
    const _PROCEDURE_ALREADY_EXISTS      = 'wamp.error.procedure_already_exists';
    const _INVALID_ARGUMENT              = 'wamp.error.invalid_argument';
    const _SYSTEM_SHUTDOWN               = 'wamp.error.system_shutdown';
    const _CLOSE_REALM                   = 'wamp.error.close_realm';
    const _GOODBYE_AND_OUT               = 'wamp.error.goodbye_and_out';
    const _NOT_AUTHORIZED                = 'wamp.error.not_authorized';
    const _AUTHORIZATION_FAILED          = 'wamp.error.authorization_failed';
    const _CANCELLED                     = 'wamp.error.canceled';
    const _OPTION_NOT_ALLOWED            = 'wamp.error.option_not_allowed';
    const _NO_ELIGIBLE_CALLEE            = 'wamp.error.no_eligible_callee';
    const _OPTION_DISALLOWED_DISCLOSE_ME = 'wamp.error.option_disallowed.disclose_me';
    const _NETWORK_FAILURE               = 'wamp.error.network_failure';
    
    private function __construct()
    {}
}
