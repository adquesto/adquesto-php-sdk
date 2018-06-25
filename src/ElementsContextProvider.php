<?php

namespace Adquesto\SDK;

class ElementsContextProvider implements ContextProvider
{
    protected $mainQuestId;
    protected $reminderQuestId;

    public function __construct($mainQuestId = null, $reminderQuestId = null)
    {
        $this->mainQuestId = $mainQuestId ? $mainQuestId : $this->generateId('q-');
        $this->reminderQuestId = $reminderQuestId ? $reminderQuestId : $this->generateId('rq-');
    }

    protected function generateId($prefix = null)
    {
        return uniqid($prefix);
    }

    public function mainQuestId()
    {
        return $this->mainQuestId;
    }

    public function reminderQuestId()
    {
        return $this->reminderQuestId;
    }

    public function values()
    {
        return array(
            '__MAIN_QUEST_ID__' => $this->mainQuestId,
            '__REMINDER_QUEST_ID__' => $this->reminderQuestId,
        );
    }
}
