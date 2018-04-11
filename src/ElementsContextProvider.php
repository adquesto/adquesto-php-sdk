<?php

namespace Adquesto\SDK;

class ElementsContextProvider implements ContextProvider
{
    private $mainQuestId;
    private $reminderQuestId;

    public function __construct($mainQuestId, $reminderQuestId)
    {
        $this->mainQuestId = $mainQuestId;
        $this->reminderQuestId = $reminderQuestId;
    }

    public function values()
    {
        return array(
            '__MAIN_QUEST_ID__' => $this->mainQuestId,
            '__REMINDER_QUEST_ID__' => $this->reminderQuestId,
        );
    }
}
