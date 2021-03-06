<?php

namespace Http\Message\Builder;

use Psr\Http\Message\ResponseInterface;

/**
 * Fills response object with values.
 */
class ResponseBuilder
{
    /**
     * The response to be built.
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Create builder for the given response.
     *
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Return response.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Add headers represented by an array of header lines.
     *
     * @param string[] $headers Response headers as array of header lines.
     *
     * @return $this
     *
     * @throws \UnexpectedValueException For invalid header values.
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function setHeadersFromArray(array $headers)
    {
        $statusLine = trim(array_shift($headers));
        $parts = explode(' ', $statusLine, 3);
        if (count($parts) < 2 || substr(strtolower($parts[0]), 0, 5) !== 'http/') {
            throw new \UnexpectedValueException(
                sprintf('"%s" is not a valid HTTP status line', $statusLine)
            );
        }

        $reasonPhrase = count($parts) > 2 ? $parts[2] : '';
        $this->response = $this->response
            ->withStatus((int) $parts[1], $reasonPhrase)
            ->withProtocolVersion(substr($parts[0], 5));

        foreach ($headers as $headerLine) {
            $headerLine = trim($headerLine);
            if ('' === $headerLine) {
                continue;
            }

            $parts = explode(':', $headerLine, 2);
            if (count($parts) !== 2) {
                throw new \UnexpectedValueException(
                    sprintf('"%s" is not a valid HTTP header line', $headerLine)
                );
            }
            $name = trim(urldecode($parts[0]));
            $value = trim(urldecode($parts[1]));
            if ($this->response->hasHeader($name)) {
                $this->response = $this->response->withAddedHeader($name, $value);
            } else {
                $this->response = $this->response->withHeader($name, $value);
            }
        }

        return $this;
    }

    /**
     * Add headers represented by a single string.
     *
     * @param string $headers Response headers as single string.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException if $headers is not a string on object with __toString()
     * @throws \UnexpectedValueException For invalid header values.
     */
    public function setHeadersFromString($headers)
    {
        if (!(is_string($headers)
            || (is_object($headers) && method_exists($headers, '__toString')))
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s expects parameter 1 to be a string, %s given',
                    __METHOD__,
                    is_object($headers) ? get_class($headers) : gettype($headers)
                )
            );
        }

        $this->setHeadersFromArray(explode("\r\n", $headers));

        return $this;
    }
}
