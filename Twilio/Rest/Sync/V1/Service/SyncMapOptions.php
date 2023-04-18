<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Sync\V1\Service;

use Twilio\Options;
use Twilio\Values;

/**
 * PLEASE NOTE that this class contains beta products that are subject to change. Use them with caution.
 */
abstract class SyncMapOptions {
    /**
     * @param string $uniqueName Human-readable name for this map
     * @param integer $ttl Time-to-live of this Map in seconds, defaults to no
     *                     expiration.
     * @return CreateSyncMapOptions Options builder
     */
    public static function create($uniqueName = Values::NONE, $ttl = Values::NONE) {
        return new CreateSyncMapOptions($uniqueName, $ttl);
    }

    /**
     * @param integer $ttl New time-to-live of this Map in seconds.
     * @return UpdateSyncMapOptions Options builder
     */
    public static function update($ttl = Values::NONE) {
        return new UpdateSyncMapOptions($ttl);
    }
}

class CreateSyncMapOptions extends Options {
    /**
     * @param string $uniqueName Human-readable name for this map
     * @param integer $ttl Time-to-live of this Map in seconds, defaults to no
     *                     expiration.
     */
    public function __construct($uniqueName = Values::NONE, $ttl = Values::NONE) {
        $this->options['uniqueName'] = $uniqueName;
        $this->options['ttl'] = $ttl;
    }

    /**
     * Human-readable name for this map
     * 
     * @param string $uniqueName Human-readable name for this map
     * @return $this Fluent Builder
     */
    public function setUniqueName($uniqueName) {
        $this->options['uniqueName'] = $uniqueName;
        return $this;
    }

    /**
     * Time-to-live of this Map in seconds, defaults to no expiration. In the range [1, 31 536 000 (1 year)], or 0 for infinity.
     * 
     * @param integer $ttl Time-to-live of this Map in seconds, defaults to no
     *                     expiration.
     * @return $this Fluent Builder
     */
    public function setTtl($ttl) {
        $this->options['ttl'] = $ttl;
        return $this;
    }

    /**
     * Provide a friendly representation
     * 
     * @return string Machine friendly representation
     */
    public function __toString() {
        $options = array();
        foreach ($this->options as $key => $value) {
            if ($value != Values::NONE) {
                $options[] = "$key=$value";
            }
        }
        return '[Twilio.Sync.V1.CreateSyncMapOptions ' . implode(' ', $options) . ']';
    }
}

class UpdateSyncMapOptions extends Options {
    /**
     * @param integer $ttl New time-to-live of this Map in seconds.
     */
    public function __construct($ttl = Values::NONE) {
        $this->options['ttl'] = $ttl;
    }

    /**
     * New time-to-live of this Map in seconds. In the range [1, 31 536 000 (1 year)], or 0 for infinity.
     * 
     * @param integer $ttl New time-to-live of this Map in seconds.
     * @return $this Fluent Builder
     */
    public function setTtl($ttl) {
        $this->options['ttl'] = $ttl;
        return $this;
    }

    /**
     * Provide a friendly representation
     * 
     * @return string Machine friendly representation
     */
    public function __toString() {
        $options = array();
        foreach ($this->options as $key => $value) {
            if ($value != Values::NONE) {
                $options[] = "$key=$value";
            }
        }
        return '[Twilio.Sync.V1.UpdateSyncMapOptions ' . implode(' ', $options) . ']';
    }
}