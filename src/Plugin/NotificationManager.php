<?php

namespace MyParcelNL\Pdk\Plugin;

class NotificationManager
{
    /**
     * @var array
     */
    private $notifications = [];

    public function add(string $message, string $level = 'info'): void
    {
        $this->notifications[] = [
            'category' => 'general',
            'id'       => md5($message),
            'level'    => $level,
            'message'  => $message,
        ];
    }

    public function addMany(array $notifications): void
    {
        foreach ($notifications as $notification) {
            $this->add($notification['message'], $notification['level']);
        }
    }

    public function get(): array
    {
        return $this->notifications;
    }

    public function has(): bool
    {
        return ! empty($this->notifications);
    }
}
