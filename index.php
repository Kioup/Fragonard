<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim(array(
    'debug' => true
));

/////////////////////////////////////////////////////

$db = mysqli_connect('localhost', 'root', '', 'mmi');
if ($db) {
    mysqli_query($db,"
    CREATE TABLE IF NOT EXISTS `articles` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) DEFAULT NULL,
      `content` text,
      `image` varchar(255) DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    mysqli_query($db,"
    CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`,`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


    $requete = mysqli_query($db, 'SELECT * FROM articles LIMIT 0, 10');

    if(mysqli_num_rows($requete) == 0) {
      mysqli_query($db,"
      INSERT INTO `articles` VALUES ('1', 'Rei oporteret aliud tam non.', 'Nunc vero inanes flatus quorundam vile esse quicquid extra urbis pomerium nascitur aestimant praeter orbos et caelibes, nec credi potest qua obsequiorum diversitate coluntur homines sine liberis Romae.', 'http://placeimg.com/320/240/tech', '2016-02-22 12:39:06'), ('2', 'Fidis sine speciem in et.', 'Qui cum venisset ob haec festinatis itineribus Antiochiam, praestrictis palatii ianuis, contempto Caesare, quem videri decuerat, ad praetorium cum pompa sollemni perrexit morbosque diu causatus nec regiam introiit nec processit in publicum, sed abditus multa in eius moliebatur exitium addens quaedam relationibus supervacua, quas subinde dimittebat ad principem.', 'http://placeimg.com/320/240/tech', '2016-02-22 12:39:06'), ('3', 'Sed deinde sunt funeribus stationes.', 'Auxerunt haec vulgi sordidioris audaciam, quod cum ingravesceret penuria commeatuum, famis et furoris inpulsu Eubuli cuiusdam inter suos clari domum ambitiosam ignibus subditis inflammavit rectoremque ut sibi iudicio imperiali addictum calcibus incessens et pugnis conculcans seminecem laniatu miserando discerpsit. post cuius lacrimosum interitum in unius exitio quisque imaginem periculi sui considerans documento recenti similia formidabat.', 'http://placeimg.com/320/240/tech', '2016-02-22 12:39:06');
      ");
    }
}

/////////////////////////////////////////////////////

$app->get('/articles', function () use ($db) {

    $requete = mysqli_query($db, 'SELECT * FROM articles LIMIT 0, 10');

    $data = array();

    while($d = mysqli_fetch_assoc($requete))
    {
    	$data[] = $d;
    }
    mysqli_free_result($requete);

    echo json_encode($data);
});

$app->get('/articles/:id', function ($id) use ($db) {
  $requete = mysqli_query($db, "SELECT * FROM articles WHERE id=$id LIMIT 1");

  $data = mysqli_fetch_assoc($requete);
  mysqli_free_result($requete);

  echo json_encode($data);
});

$app->post('/articles', function () use ($app, $db) {
  $data = json_decode($app->request->getBody());

  if(isset($data->title, $data->content)) {
    $res  = mysqli_query($db, "INSERT INTO articles(title, content) VALUES ('{$data->title}', '{$data->content}');");

    if($res) {
      echo json_encode(array(
        'error' => false
      ));
      exit;
    }
  }

  echo json_encode(array(
    'error' => true
  ));
});

$app->put('/articles/:id', function ($id) use ($app, $db) {
  $data = json_decode($app->request->getBody());

  if(isset($data->title, $data->content)) {
    $res  = mysqli_query($db, "UPDATE articles SET title = '{$data->title}', content = '{$data->content}' WHERE id=$id");

    if($res) {
      echo json_encode(array(
        'error' => false
      ));
      exit;
    }
  }

  echo json_encode(array(
    'error' => true
  ));
});

$app->delete('/articles/:id', function ($id) use ($db) {
  mysqli_query($db, "DELETE FROM articles WHERE id=$id;");

  echo json_encode(array(
    'error' => false
  ));
});

$app->post('/users', function () use ($app, $db) {
  $data = json_decode($app->request->getBody());

  if(isset($data->email, $data->password)) {

    $data->password = md5($data->password);
    $res  = mysqli_query($db, "INSERT INTO users(email, password) VALUES ('{$data->email}', '{$data->password}');");

    if($res) {
      echo json_encode(array(
        'error' => false
      ));
      exit;
    }
  }

  echo json_encode(array(
    'error' => true
  ));
});

$app->put('/users', function () use ($app, $db) {
  $data = json_decode($app->request->getBody());

  if(isset($data->email, $data->password)) {
    $data->password = md5($data->password);
    $res  = mysqli_query($db, "UPDATE users SET password = '{$data->password}' WHERE email='{$data->email}'");

    if($res) {
      echo json_encode(array(
        'error' => false
      ));
      exit;
    }
  }

  echo json_encode(array(
    'error' => true
  ));
});


$app->response()->header("Content-Type", "application/json");

$app->response()->header('Access-Control-Allow-Origin', '*');
$app->response()->header('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE');
$app->response()->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

$app->run();
