<?php

/**
 * The MIT License (MIT).
 *
 * Copyright (c) 2015 Antoine Colmard
 *               2015 Nicolas Prugne
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace QuidNovi\Controller;

use Negotiation\FormatNegotiator;
use QuidNovi\QuidNovi;

abstract class AbstractController
{
    /**
     * @var QuidNovi
     */
    protected $app;

    /**
     * @var \Slim\Http\Request
     */
    protected $request;

    /**
     * @var \Slim\Http\Response
     */
    protected $response;

    public function __construct(QuidNovi $app)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->response = $app->response;
        $this->createRoutes();
    }

    abstract public function createRoutes();

    /**
     * Build a response with given status and responseBody.
     * Response body is encoded with best matching available content type.
     *
     * @param $status int Response status.
     * @param $responseBody mixed Response body.
     */
    public function buildResponse($status, $responseBody)
    {
        $this->response->setStatus($status);
        $encoders = array(
            'application/json' => 'json_encode',
        );
        $contentType = $this->getBestMatchingContentType();

        $encoder = $encoders[$contentType];
        if (null === $encoder) {
            $this->app->halt(406);
        }
        $encodedBody = $encoder($responseBody);
        $this->response->setBody($encodedBody);
    }

    /**
     * Parse http Accept header in request and returns best matching content type to return.
     *
     * @return string content type.
     */
    public function getBestMatchingContentType()
    {
        $negotiator = new FormatNegotiator();
        $priorities = array('application/json');
        $bestHeader = $negotiator->getBest($this->request->headers('HTTP_ACCEPT'), $priorities);

        return $bestHeader->getValue();
    }
}
