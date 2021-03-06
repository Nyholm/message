<?php

namespace spec\Http\Message\Authentication;

use Psr\Http\Message\RequestInterface;
use PhpSpec\ObjectBehavior;

class BearerSpec extends ObjectBehavior
{
    use AuthenticationBehavior;

    function let()
    {
        $this->beConstructedWith('token');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Message\Authentication\Bearer');
    }

    function it_authenticates_a_request(RequestInterface $request, RequestInterface $newRequest)
    {
        $request->withHeader('Authorization', 'Bearer token')->willReturn($newRequest);

        $this->authenticate($request)->shouldReturn($newRequest);
    }
}
