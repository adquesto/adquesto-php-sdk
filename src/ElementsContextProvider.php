<?php

namespace Adquesto\SDK;

class ElementsContextProvider implements ContextProvider
{
    protected $mainQuestId;
    protected $reminderQuestId;
    protected $isDraft;

    public function __construct($mainQuestId = null, $reminderQuestId = null, $isDraft = false)
    {
        $this->mainQuestId = $mainQuestId ? $mainQuestId : $this->generateId('q-');
        $this->reminderQuestId = $reminderQuestId ? $reminderQuestId : $this->generateId('rq-');
        $this->isDraft = $isDraft;
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
            '__IS_PUBLISHED__' => $this->isDraft == false,
        );
    }
}
