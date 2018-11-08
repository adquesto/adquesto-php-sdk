<?php

namespace Adquesto\SDK;

class PositioningSettings
{
    const STRATEGY_UPPER = 'upper';
    const STRATEGY_LOWER = 'lower';

    protected $mainNumberOfChars;
    protected $reminderNumberOfChars;
    protected $mediaCharPoints;

    public function __construct($mainNumberOfChars, $reminderNumberOfChars, $mediaCharPoints)
    {
        $this->mainNumberOfChars = $mainNumberOfChars;
        $this->reminderNumberOfChars = $reminderNumberOfChars;
        $this->mediaCharPoints = $mediaCharPoints;
    }

    /**
     * @return mixed
     */
    public function getMainNumberOfChars()
    {
        return $this->mainNumberOfChars;
    }

    /**
     * @return mixed
     */
    public function getReminderNumberOfChars()
    {
        return $this->reminderNumberOfChars;
    }

    /**
     * @return mixed
     */
    public function getMediaCharPoints()
    {
        return $this->mediaCharPoints;
    }

    public static function factory($strategy)
    {
        if ($strategy == self::STRATEGY_UPPER) {
            return new self(500, 1000, 300);
        } elseif ($strategy == self::STRATEGY_LOWER) {
            return new self(1200, 1200, 500);
        }

        throw new \RuntimeException('Invalid positioning strategy');
    }
}
