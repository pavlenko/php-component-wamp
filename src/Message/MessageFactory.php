<?php

namespace PE\Component\WAMP\Message;

final class MessageFactory
{
    /**
     * @param array $data
     *
     * @return Message
     *
     * @throws \InvalidArgumentException If input data invalid
     */
    public static function createFromArray(array $data): Message
    {
        if ($data !== array_values($data)) {
            throw new \InvalidArgumentException('Invalid WAMP message format');
        }

        list($type, $arg0, $arg1, $arg2, $arg3, $arg4, $arg5) = array_pad($data, 7, null);

        switch ($type) {
            case Message::CODE_ABORT:
                // [ABORT, Details|dict, Reason|uri]
                return new AbortMessage($arg0, $arg1);
            case Message::CODE_HELLO:
                // [HELLO, Realm|uri, Details|dict]
                return new HelloMessage($arg0, $arg1);
            case Message::CODE_SUBSCRIBE:
                // [SUBSCRIBE, Request|id, Options|dict, Topic|uri]
                return new SubscribeMessage($arg0, $arg1, $arg2);
            case Message::CODE_UNSUBSCRIBE:
                // [UNSUBSCRIBE, Request|id, SUBSCRIBED.Subscription|id]
                return new UnsubscribeMessage($arg0, $arg1);
            case Message::CODE_PUBLISH:
                // [PUBLISH, Request|id, Options|dict, Topic|uri]
                // [PUBLISH, Request|id, Options|dict, Topic|uri, Arguments|list]
                // [PUBLISH, Request|id, Options|dict, Topic|uri, Arguments|list, ArgumentsKw|dict]
                return new PublishMessage($arg0, $arg1, $arg2, $arg3, $arg4);
            case Message::CODE_GOODBYE:
                // [GOODBYE, Details|dict, Reason|uri]
                return new GoodbyeMessage($arg0, $arg1);
            case Message::CODE_AUTHENTICATE:
                // [AUTHENTICATE, Signature|string, Extra|dict]
                return new AuthenticateMessage($arg0, $arg1);
            case Message::CODE_REGISTER:
                // [REGISTER, Request|id, Options|dict, Procedure|uri]
                return new RegisterMessage($arg0, $arg1, $arg2);
            case Message::CODE_UNREGISTER:
                // [UNREGISTER, Request|id, REGISTERED.Registration|id]
                return new UnregisterMessage($arg0, $arg1);
            case Message::CODE_UNREGISTERED:
                // [UNREGISTERED, UNREGISTER.Request|id]
                return new UnregisteredMessage($arg0);
            case Message::CODE_CALL:
                // [CALL, Request|id, Options|dict, Procedure|uri]
                // [CALL, Request|id, Options|dict, Procedure|uri, Arguments|list]
                // [CALL, Request|id, Options|dict, Procedure|uri, Arguments|list, ArgumentsKw|dict]
                return new CallMessage($arg0, $arg1, $arg2, $arg3, $arg4);
            case Message::CODE_YIELD:
                // [YIELD, INVOCATION.Request|id, Options|dict]
                // [YIELD, INVOCATION.Request|id, Options|dict, Arguments|list]
                // [YIELD, INVOCATION.Request|id, Options|dict, Arguments|list, ArgumentsKw|dict]
                return new YieldMessage($arg0, $arg1, $arg2, $arg3);
            case Message::CODE_WELCOME:
                // [WELCOME, Session|id, Details|dict]
                return new WelcomeMessage($arg0, $arg1);
            case Message::CODE_SUBSCRIBED:
                // [SUBSCRIBED, SUBSCRIBE.Request|id, Subscription|id]
                return new SubscribedMessage($arg0, $arg1);
            case Message::CODE_UNSUBSCRIBED:
                // [UNSUBSCRIBED, UNSUBSCRIBE.Request|id]
                return new UnsubscribedMessage($arg0);
            case Message::CODE_EVENT:
                // [EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict]
                // [EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict, PUBLISH.Arguments|list]
                // [EVENT, SUBSCRIBED.Subscription|id, PUBLISHED.Publication|id, Details|dict, PUBLISH.Arguments|list, PUBLISH.ArgumentsKw|dict]
                return new EventMessage($arg0, $arg1, $arg2, $arg3, $arg4);
            case Message::CODE_REGISTERED:
                // [REGISTERED, REGISTER.Request|id, Registration|id]
                return new RegisteredMessage($arg0, $arg1);
            case Message::CODE_INVOCATION:
                // [INVOCATION, Request|id, REGISTERED.Registration|id, Details|dict]
                // [INVOCATION, Request|id, REGISTERED.Registration|id, Details|dict, CALL.Arguments|list]
                // [INVOCATION, Request|id, REGISTERED.Registration|id, Details|dict, CALL.Arguments|list, CALL.ArgumentsKw|dict]
                return new InvocationMessage($arg0, $arg1, $arg2, $arg3, $arg4);
            case Message::CODE_RESULT:
                // [RESULT, CALL.Request|id, Details|dict]
                // [RESULT, CALL.Request|id, Details|dict, YIELD.Arguments|list]
                // [RESULT, CALL.Request|id, Details|dict, YIELD.Arguments|list, YIELD.ArgumentsKw|dict]
                return new ResultMessage($arg0, $arg1, $arg2, $arg3);
            case Message::CODE_PUBLISHED:
                // [PUBLISHED, PUBLISH.Request|id, Publication|id]
                return new PublishedMessage($arg0, $arg1);
            case Message::CODE_CHALLENGE:
                // [CHALLENGE, AuthMethod|string, Extra|dict]
                return new ChallengeMessage($arg0, $arg1);
            case Message::CODE_HEARTBEAT:
                // [HEARTBEAT, IncomingSeq|integer, OutgoingSeq|integer
                // [HEARTBEAT, IncomingSeq|integer, OutgoingSeq|integer, Discard|string]
                return new HeartbeatMessage($arg0, $arg1, $arg2);
            case Message::CODE_CANCEL:
                // [CANCEL, CALL.Request|id, Options|dict]
                return new CancelMessage($arg0, $arg1);
            case Message::CODE_INTERRUPT:
                // [INTERRUPT, INVOCATION.Request|id, Options|dict]
                return new InterruptMessage($arg0, $arg1);
            case Message::CODE_ERROR:
                // [ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri]
                // [ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri, Arguments|list]
                // [ERROR, REQUEST.Type|int, REQUEST.Request|id, Details|dict, Error|uri, Arguments|list, ArgumentsKw|dict]
                return new ErrorMessage($arg0, $arg1, $arg2, $arg3, $arg4, $arg5);
            default:
                throw new \InvalidArgumentException('Unknown message type: ' . $type);
        }
    }

    /**
     * @param Message     $message
     * @param string|null $errorUri
     *
     * @return ErrorMessage
     *
     * @throws \InvalidArgumentException If the message didn't have a request id
     */
    public static function createErrorMessageFromMessage(Message $message, string $errorUri = null): ErrorMessage
    {
        if ($errorUri === null) {
            $errorUri = Message::ERROR_UNKNOWN;
        }

        if (method_exists($message, 'getRequestId')) {
            return new ErrorMessage($message->getCode(), $message->getRequestId(), [], $errorUri);
        }

        throw new \InvalidArgumentException(
            "Can't create an error message because the message didn't have a request id"
        );
    }
}