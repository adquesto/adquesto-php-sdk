<?php

namespace Adquesto\SDK;

class DraftExplainContextProvider implements ContextProvider
{
    const REASON_MASTER_SWITCH_OFF = 'master_switch_off';
    const REASON_MISSING_CONTENT = 'content_too_short';
    const REASON_CONFIGURATION = 'configuration';
    const REASON_EXCLUDED_POST = 'excluded_post';

    private $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function values()
    {
        return array(
            '__DRAFT_EXPLAIN__' => $this->reason,
        );
    }
}
