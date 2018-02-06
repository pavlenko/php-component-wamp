**Usage**
```php
// Instantiate transport
$transport = new WebSocketTransport('127.0.0.1', 8080, true);

// Instantiate client
$client = new Client('realm1', $transport);

// Add client roles
$client->addRole();
$client->addRole();
$client->addRole();
$client->addRole();

// Add client callbacks
$client->on(Events::CONNECTION_OPEN, function (ConnectionEvent $event) {
    $event->getSession()->subscribe(
        'com.myapp.mytopic1',
        function (array $args, array $argsKw, array $details, int $publicationID) {
            // Do some stuff
        }
    );
})

// Start client
$client->start();
```