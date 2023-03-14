# TODO
- Common
  - Remove create object instances everywhere when possible
- Authentication
  - Transport-level Authentication (router only)
    - TLS Certificate Authentication
- maybe change session logic:
  - set id on create session
  - add isAuthorized flag for check later
- Broker
  - see \Ratchet\Wamp\TopicManager and \Ratchet\Wamp\Topic as ref
    for refactoring: create topics with subscriptions
    instead of global subscriptions list
- Session:
  - add some user identity for identify users instead of connections

## Message flow
HELLO
```text
,------.          ,------.
|Client|          |Router|
`--+---'          `--+---'
   |      HELLO      |
   |---------------->|
   |                 |
   |     WELCOME     |
   |<----------------|
,--+---.          ,--+---.
|Client|          |Router|
`------'          `------'
```
ABORT
```text
,------.          ,------.
|Client|          |Router|
`--+---'          `--+---'
   |      HELLO      |
   |---------------->|
   |                 |
   |      ABORT      |
   |<----------------|
,--+---.          ,--+---.
|Client|          |Router|
`------'          `------'
```
CLOSE from client
```text
,------.          ,------.
|Client|          |Router|
`--+---'          `--+---'
   |     GOODBYE     |
   |---------------->|
   |                 |
   |     GOODBYE     |
   |<----------------|
,--+---.          ,--+---.
|Client|          |Router|
`------'          `------'
```
CLOSE from router
```text
,------.          ,------.
|Client|          |Router|
`--+---'          `--+---'
   |     GOODBYE     |
   |<----------------|
   |                 |
   |     GOODBYE     |
   |---------------->|
,--+---.          ,--+---.
|Client|          |Router|
`------'          `------'
```
Subscribing and Unsubscribing
```text
,---------.          ,------.             ,----------.
|Publisher|          |Broker|             |Subscriber|
`----+----'          `--+---'             `----+-----'
     |                  |       SUBSCRIBE      |
     |                  |<---------------------|
     |                  |                      |
     |                  |  SUBSCRIBED or ERROR |
     |                  |--------------------->|
     |                  |                      |
     |                  |                      |
     |                  |      UNSUBSCRIBE     |
     |                  |<---------------------|
     |                  |                      |
     |                  | UNSUBSCRIBED or ERROR|
     |                  |--------------------->|
,----+----.          ,--+---.             ,----+-----.
|Publisher|          |Broker|             |Subscriber|
`---------'          `------'             `----------'
```
Publishing and Events
```text
,---------.          ,------.          ,----------.
|Publisher|          |Broker|          |Subscriber|
`----+----'          `--+---'          `----+-----'
     |     PUBLISH      |                   |
     |----------------->|                   |
     |                  |                   |
     |PUBLISHED or ERROR|                   |
     |<-----------------|                   |
     |                  |                   |
     |                  |       EVENT       |
     |                  |------------------>|
,----+----.          ,--+---.          ,----+-----.
|Publisher|          |Broker|          |Subscriber|
`---------'          `------'          `----------'
```
Registering and Unregistering
```text
,------.          ,------.                ,------.
|Caller|          |Dealer|                |Callee|
`--+---'          `--+---'                `--+---'
   |                 |       REGISTER        |
   |                 |<----------------------|
   |                 |                       |
   |                 |  REGISTERED or ERROR  |
   |                 |---------------------->|
   |                 |                       |
   |                 |                       |
   |                 |      UNREGISTER       |
   |                 |<----------------------|
   |                 |                       |
   |                 | UNREGISTERED or ERROR |
   |                 |---------------------->|
,--+---.          ,--+---.                ,--+---.
|Caller|          |Dealer|                |Callee|
`------'          `------'                `------'
```
Calling and Invocations
```text
,------.          ,------.         ,------.
|Caller|          |Dealer|         |Callee|
`--+---'          `--+---'         `--+---'
   |       CALL      |                |
   |---------------->|                |
   |                 |                |
   |                 |   INVOCATION   |
   |                 |--------------->|
   |                 |                |
   |                 | YIELD or ERROR |
   |                 |<---------------|
   |                 |                |
   | RESULT or ERROR |                |
   |<----------------|                |
,--+---.          ,--+---.         ,--+---.
|Caller|          |Dealer|         |Callee|
`------'          `------'         `------'
```
Progressive Call Results
```text
,------.            ,------.          ,------.
|Caller|            |Dealer|          |Callee|
`--+---'            `--+---'          `--+---'
   |       CALL        |                  |
   |------------------>|                  |
   |                   |                  |
   |                   |    INVOCATION    |
   |                   |----------------->|
   |                   |                  |
   |                   | YIELD (progress) |
   |                   |<-----------------|
   |                   |                  |
   | RESULT (progress) |                  |
   |<------------------|                  |
   |                   |                  |
   |                   | YIELD (progress) |
   |                   |<-----------------|
   |                   |                  |
   | RESULT (progress) |                  |
   |<------------------|                  |
   |                   |                  |
   |                   |                  |
   |       ...         |       ...        |
   |                   |                  |
   |                   |  YIELD or ERROR  |
   |                   |<-----------------|
   |                   |                  |
   |  RESULT or ERROR  |                  |
   |<------------------|                  |
,--+---.            ,--+---.          ,--+---.
|Caller|            |Dealer|          |Callee|
`------'            `------'          `------'
```
A call may be cancelled at the Callee
```text
,------.          ,------.          ,------.
|Caller|          |Dealer|          |Callee|
`--+---'          `--+---'          `--+---'
   |       CALL      |                 |    
   |---------------->|                 |    
   |                 |                 |    
   |                 |    INVOCATION   |    
   |                 |---------------->|    
   |                 |                 |    
   |      CANCEL     |                 |    
   |---------------->|                 |    
   |                 |                 |    
   |                 |    INTERRUPT    |    
   |                 |---------------->|    
   |                 |                 |    
   |                 |      ERROR      |    
   |                 |<----------------|    
   |                 |                 |    
   |      ERROR      |                 |    
   |<----------------|                 |    
,--+---.          ,--+---.          ,--+---.
|Caller|          |Dealer|          |Callee|
`------'          `------'          `------'
```
A call may be cancelled at the Dealer
```text
,------.          ,------.          ,------.
|Caller|          |Dealer|          |Callee|
`--+---'          `--+---'          `--+---'
   |       CALL      |                 |    
   |---------------->|                 |    
   |                 |                 |    
   |                 |    INVOCATION   |    
   |                 |---------------->|    
   |                 |                 |    
   |      CANCEL     |                 |    
   |---------------->|                 |    
   |                 |                 |    
   |      ERROR      |                 |    
   |<----------------|                 |    
   |                 |                 |    
   |                 |    INTERRUPT    |    
   |                 |---------------->|    
   |                 |                 |    
   |                 |      ERROR      |    
   |                 |<----------------|    
,--+---.          ,--+---.          ,--+---.
|Caller|          |Dealer|          |Callee|
`------'          `------'          `------'
```
WAMP-level Authentication
```text
,------.           ,------.
|Client|           |Router|
`--+---'           `--+---'
   |      HELLO       |    
   |----------------->|    
   |                  |    
   |    CHALLENGE     |    
   |<-----------------|    
   |                  |    
   |   AUTHENTICATE   |    
   |----------------->|    
   |                  |    
   | WELCOME or ABORT |    
   |<-----------------|    
,--+---.           ,--+---.
|Client|           |Router|
`------'           `------'
```

## Advanced
Pattern-based subscriptions
```text
                                          Subscription
                          +------+         +--------+           +----------+
                          |      |--Event->| Exact  |--------+->|Subscriber|
                          |      |         +--------+        |  +----------+
                          |      |                           | 
+---------+               |      |         +--------+        |  +----------+
|Publisher|--Publication->|Broker|--Event->|Wildcard|----+-> `->|Subscriber|
+---------+               |      |         +--------+    |      +----------+
                          |      |                       |
                          |      |         +--------+    |      +----------+                          
                          |      |--Event->| Prefix |--> `----->|Subscriber|
                          +------+         +--------+           +----------+
```

## Features
**RPC Features**

| Feature                    | Status | P   | B   | S   | Cr  |  D  | Ce  |
|----------------------------|--------|-----|-----|-----|:---:|:---:|:---:|
| progressive_call_results   | beta   |     |     |     |  X  |  X  |  X  |
| progressive_calls          | sketch |     |     |     |  X  |  X  |  X  |
| call_timeout               | alpha  |     |     |     |  X  |  X  |  X  |
| call_canceling             | alpha  |     |     |     |  X  |  X  |  X  |
| caller_identification      | alpha  |     |     |     |  X  |  X  |  X  |
| call_trustlevels           | alpha  |     |     |     |     |  X  |  X  |
| registration_meta_api      | beta   |     |     |     |     |  X  |     |
| pattern_based_registration | beta   |     |     |     |     |  X  |  X  |
| shared_registration        | beta   |     |     |     |     |  X  |  X  |
| sharded_registration       | alpha  |     |     |     |     |  X  |  X  |
| registration_revocation    | alpha  |     |     |     |     |  X  |  X  |
| procedure_reflection       | sketch |     |     |     |     |  X  |     |

&nbsp;

**PubSub Features**

| Feature                       | Status |  P  |  B  |  S  | Cr  | D   | Ce  |
|-------------------------------|--------|:---:|:---:|:---:|-----|-----|-----|
| subscriber_blackwhite_listing | stable |  X  |  X  |     |     |     |     |
| publisher_exclusion           | stable |  X  |  X  |     |     |     |     |
| publisher_identification      | alpha  |  X  |  X  |  X  |     |     |     |
| publication_trustlevels       | alpha  |     |  X  |  X  |     |     |     |
| subscription_meta_api         | beta   |     |  X  |     |     |     |     |
| pattern_based_subscription    | beta   |     |  X  |  X  |     |     |     |
| sharded_subscription          | alpha  |     |  X  |  X  |     |     |     |
| event_history                 | alpha  |     |  X  |  X  |     |     |     |
| topic_reflection              | sketch |     |  X  |     |     |     |     |

&nbsp;

**Other Advanced Features**

| Feature                           | Status |
|-----------------------------------|--------|
| challenge-response authentication | beta   |
| cookie authentication             | beta   |
| ticket authentication             | beta   |
| rawsocket transport               | stable |
| batched WS transport              | sketch |
| longpoll transport                | beta   |
| session meta api                  | beta   |
