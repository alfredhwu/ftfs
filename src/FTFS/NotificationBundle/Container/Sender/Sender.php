<?php
namespace FTFS\NotificationBundle\Container\Sender;

use FTFS\NotificationBundle\Container\Sender\Agent\SenderAgentInterface;

/**
 * Sender service.
 */
class Sender
{
    /**
     * An array of sending agents. Key is the alias.
     *
     * @var array
     */
    protected $agents = array();

    /**
     * @param array $agents
     *
     * init agents with parameters from services
     */
    public function __construct(array $agents)
    {
        foreach ($agents as $key => $agent) {
            if (!$agent instanceof SenderAgentInterface) {
                throw new \InvalidArgumentException(sprintf('Agent %s must implement AgentInterface', get_class($agent)));
            }
        }

        $this->agents = $agents;
    }

    /**
     * Returns an array of agent aliases that can be used to send
     * a notification.
     *
     * @return array
     */
    public function getAgentAliases()
    {
        return array_keys($this->agents);
    }

    /**
     * Gets an agent by its alias, or throws an exception when
     * that agent does not exist.
     *
     * @param string $alias
     * @return \FTFS\NotificationBundle\Container\Sender\Agent\AgentInterface
     * @throws \InvalidArgumentException when the alias doesnt exist
     */
    protected function getAgent($alias)
    {
        if (!isset($this->agents[$alias])) {
            throw new \InvalidArgumentException(sprintf('Alias "%s" doesnt exist', $alias));
        }

        return $this->agents[$alias];
    }

    /**
     * Sorts the array of notifications by notification method
     * and sends each in bulk to the appropriate agent for sending.
     *
     * @param array $notifications
     */
    public function send(array $notifications)
    {
        $sorted = array();
        foreach ($notifications as $notification) {
            $sorted[$notification->getMethod()->getName()][] = $notification;
        }

        foreach ($sorted as $method => $notifications) {
            $this->getAgent($method)->sendBulk($notifications);
        }
    }
}
