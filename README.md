# ProfileNetwork
Profile Cloud Service

## Usage
### Load profiles
```php
<?php
  $friends = ['abcd1234', 'efgh5678']; // Put the Profile codes
  foreach ($friends as $jsoncode) {
    $data = json_decode(file_get_contents("http://<Your Profile Cloud Page>/".$jsoncode.".json"), true);
    // You can use nickname, description, homepage, image
?>
    <img src="<?php echo $data['image'] ?>">
    <a href="<?php echo $data['homepage'] ?>" target="_blank"><?php echo $data['nickname'] ?></a>
    <p><?php echo $data['description'] ?></p>
<?php
  }
?>
```
