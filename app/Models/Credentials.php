<?php

namespace App\Models;

use App\Exceptions\CredentialException;

class Credentials
{
    /**
     * @var object
     */
    protected $credentials;

    /**
     * @var string
     */
    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->credentials = (object) [];
    }

    /**
     * Returns the value for a specified credential.
     *
     * @param string $credentialKey
     *
     * @return null|string
     */
    public function get(string $credentialKey): ?string
    {
        return $this->credentials->{$credentialKey} ?? null;
    }

    /**
     * Loads saved credentials. If none are stored, an initial set is used.
     */
    public function loadOrCreate(): void
    {
        $fileContents = (array) $this->getFileContents();
        $defaultCredentials = [
            'PLANNING_CENTER_APPLICATION_ID' => '',
            'PLANNING_CENTER_SECRET' => '',
            'PLANNING_CENTER_SERVICE_TYPE_ID' => '',
            'SPOTIFY_CLIENT_ID' => '',
            'SPOTIFY_CLIENT_SECRET' => '',
        ];

        $this->credentials = (object) array_merge(
            $defaultCredentials,
            $fileContents
        );

        $this->confirmOrGet();
    }

    /**
     * Saves a new credential.
     *
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value): void
    {
        $this->credentials->{$key} = $value;
        $this->save();
    }

    /**
     * Confirms that every credential has a value. If it does not, it will ask the user for input.
     */
    protected function confirmOrGet(): void
    {
        foreach ($this->credentials as $credential => $value) {
            if ($value !== '') {
                continue;
            }

            echo "Please enter {$credential}: ";
            $this->credentials->{$credential} = readline();
            $this->save();

            if ($this->credentials->{$credential} === '') {
                throw new CredentialException($credential);
            }
        }
    }

    /**
     * Returns all saved credentials.
     *
     * @return object
     */
    protected function getFileContents(): object
    {
        if (!file_exists($this->filePath)) {
            return json_decode('{}');
        }

        return json_decode(
            file_get_contents($this->filePath),
        );
    }

    /**
     * Updates the credentials file with all current values.
     */
    protected function save(): void
    {
        $directory = dirname($this->filePath);

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->filePath, json_encode($this->credentials, JSON_PRETTY_PRINT));
    }
}
