<?php

namespace SonarSoftware\Poller\Models;

use InvalidArgumentException;
use SonarSoftware\Poller\Formatters\Formatter;

class DeviceInterface
{
    private $up;
    private $metadata = [];
    private $macAddress;
    private $connectedMacsLayer2 = [];
    private $connectedMacsLayer3 = [];
    private $description;

    const LAYER2 = "l2";
    const LAYER3 = "l3";

    /**
     * Convert this interface to an array
     * @return array
     */
    public function toArray():array
    {
        return [
            'up' => (bool)$this->up,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'mac_address' => $this->macAddress,
            'connected_l2' => $this->connectedMacsLayer2,
            'connected_l3' => $this->connectedMacsLayer3,
        ];
    }

    /**
     * Set the name/description of the interface
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Get the name/description of the interface
     * @return mixed
     */
    public function getDescription():string
    {
        return $this->description;
    }

    /**
     * Set the status of the port (if it's up, this is true)
     * @param bool $status
     */
    public function setUp(bool $status)
    {
        $this->up = $status;
    }

    /**
     * Get up, get up, to get down
     * @return mixed
     */
    public function getUp():bool
    {
        return $this->up;
    }


    /**
     * The format of metadata provided can be anything, it's just an array of data to be displayed in the application.
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadata():array
    {
        return $this->metadata;
    }

    /**
     * Set the interface MAC
     * @param $macAddress
     */
    public function setMacAddress($macAddress)
    {
        $macAddress = Formatter::formatMac($macAddress);
        if ($this->validateMac($macAddress) === false)
        {
            throw new InvalidArgumentException($macAddress . " is not a valid MAC address.");
        }
        $this->macAddress = $macAddress;
    }

    /**
     * @return string
     */
    public function getMacAddress():string
    {
        return $this->macAddress;
    }

    /**
     * @param array $connectedMacs
     * @param string $layer - One of $this::LAYER2, $this::LAYER3
     */
    public function setConnectedMacs(array $connectedMacs, string $layer)
    {
        if (!in_array($layer,[$this::LAYER2, $this::LAYER3]))
        {
            throw new InvalidArgumentException("Layer must be one of the layer constants.");
        }

        $cleanedMacs = [];
        foreach ($connectedMacs as $connectedMac)
        {
            $connectedMac = Formatter::formatMac($connectedMac);
            if ($this->validateMac($connectedMac) === false)
            {
                throw new InvalidArgumentException($connectedMac . " is not a valid MAC address.");
            }
            array_push($cleanedMacs, $connectedMac);
        }

        switch ($layer)
        {
            case $this::LAYER2:
                $this->connectedMacsLayer2 = $cleanedMacs;
                break;
            case $this::LAYER3:
                $this->connectedMacsLayer3 = $cleanedMacs;
                break;
        }
    }

    /**
     * Returns an array of connected MAC addresses
     * @return array
     */
    public function getConnectedMacs():array
    {
        return $this->connectedMacs;
    }

    /**
     * @param $mac
     * @return bool
     */
    private function validateMac(string $mac):bool
    {
        return preg_match('/^([A-F0-9]{2}:){5}[A-F0-9]{2}$/', $mac) == 1;
    }
}