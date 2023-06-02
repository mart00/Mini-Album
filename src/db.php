<?php
$servername = "db";
$username = "root";
$password = "example";
$dbName = "minigallerij";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbName);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// $val = mysqli_query('SELECT 1 FROM images LIMIT 1');
//
// if($val !== FALSE)  {
//   CREATE TABLE `images` (
//  `pictureId` int NOT NULL AUTO_INCREMENT,
//  `naam` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
//  `album` int NOT NULL,
//  `locatie` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
//  `albumLaag` int NOT NULL,
//  `albumId` int NOT NULL,
//  `motherAlbumId` int NOT NULL,
//  PRIMARY KEY (`pictureId`)
// );
// }


//standaard album data meegeven / in database zetten
$coolNames = array("Cool Images","Cool Frog","Cool Cat","Cool Man");
$coolAlbum = array("1","0","0","0");
$coolLocaties = array("fotos\standaard\cool.jpg","fotos\standaard\coolcat.jpg","fotos\standaard\coolfrig.jpg","fotos\standaard\coolman.png");
$coolAlbumLaag = array("0","1","1","1");
$coolAlbumId = array("1","1","1","1");
$motherAlbumId = array("0","1","1","1");

$query = "SELECT * FROM images";
$result = mysqli_query($conn, $query) or die;

if(mysqli_num_rows($result) == 0){
  for ($i=0; $i < sizeof($coolAlbum); $i++) {
    $stmt = $conn->prepare("INSERT INTO images (naam, album, locatie, albumLaag, albumId,motherAlbumId) VALUES (?, ?, ?, ?, ?,?)");
    $stmt->bind_param("ssssss", $coolNames[$i], $coolAlbum[$i], $coolLocaties[$i], $coolAlbumLaag[$i], $coolAlbumId[$i], $motherAlbumId[$i]);
    $stmt->execute();
  }
}

//functie voor het verwijderen van alle afbeeldingen was handig voor het resetten van de website
function deleteAll($conn){
  $getPicturesToDelete = "SELECT * FROM images WHERE pictureId NOT IN (1,3,4,2)";
  $PicturesToDelete = mysqli_query($conn, $getPicturesToDelete) or die;
  while ($toDelete = mysqli_fetch_array($PicturesToDelete)) {
    deletePictures($conn,$toDelete['pictureId']);
  }

  $query = "DELETE FROM images WHERE pictureId > 4";
  $result = mysqli_query($conn, $query) or die;
  header('refresh:.5;url=http://localhost/index.php');
}

//als de delete knop ingedrukt word check je of het een album is en verwijder je alle afbeeldingen die er in zitten
//en of delete je de afbeelding zelf
if (isset($_POST['deleteSingle'])){
    $pictureId = $_POST['pictureId'];
    $albumId = $_POST['albumId'];
    $query = "SELECT * FROM images WHERE pictureId = $pictureId AND album = 1";
    $result = mysqli_query($conn, $query) or die;
    if (mysqli_num_rows($result) > 0) {
      $getPicturesToDelete = "SELECT * FROM images WHERE motherAlbumId = $albumId ";
      $PicturesToDelete = mysqli_query($conn, $getPicturesToDelete) or die;
      while ($toDelete = mysqli_fetch_array($PicturesToDelete)) {
        deletePictures($conn,$toDelete['pictureId']);
      }
      $toDeleteQuery = "DELETE FROM images WHERE motherAlbumId =  $albumId";
      $result = mysqli_query($conn, $toDeleteQuery) or die;
    }
    deletePictures($conn,$pictureId);
    $getPicturesToDelete = "DELETE FROM images WHERE pictureId = $pictureId";
    $PicturesToDelete = mysqli_query($conn, $getPicturesToDelete) or die;
    $motherAlbumId = $_POST['motherAlbumId'];
    header('refresh:.5;url=http://localhost/index.php?id='.$motherAlbumId.'');
  }
//functie voor het toevoegen van afbeeldingen in de database
function insertDb($conn, $fNaam, $album, $fLocatie, $albumLaag, $albumId, $motherAlbumId){
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $stmt = $conn->prepare("INSERT INTO images (naam, album, locatie, albumLaag, albumId, motherAlbumId) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fNaam, $album, $fLocatie, $albumLaag,$albumId,$motherAlbumId);
    $stmt->execute();
}
//hiermee worden alle albums opgeroepen en weergegeven
function getGallery($conn, $albumLaag, $motherAlbumId){
    // echo "<p>".$albumLaag,$motherAlbumId."/haha</p>";
    $stmt = $conn->prepare('SELECT * FROM images WHERE albumLaag = ? AND motherAlbumId = ? AND album = 1');
    $stmt->bind_param('ss', $albumLaag, $motherAlbumId);
    $stmt->execute();
    $gallerij = $stmt->get_result();
    while($row = $gallerij->fetch_assoc()){
        echo '
        <div class="lijst">
            <form id="deleteSingle" name="deleteSingle" action="db.php" method="post" enctype="multipart/form-data">
              <a class="'.$row["albumId"].'" id="changeAlbum" href="index.php?id='.$row["albumId"].'"><img src="'.ltrim($row['locatie'], "/var/www/html/").'"></a>
              <div class="underImage">
                <p class="titel">'.$row["naam"].'</p>
                <input hidden type="text" id="motherAlbumId" name="albumId" value="'.$row["motherAlbumId"].'">
                <input hidden type="text" id="albumId" name="albumId" value="'.$row["albumId"].'">
                <input hidden type="text" id="pictureId" name="pictureId" value="'.$row["pictureId"].'">
                <input type="submit" class="deleteButton" name="deleteSingle" value="DELETE">
              </div>
            </form>
        </div>
        ';
    }
    return $albumLaag;
}
// zelfde als hierboven maar met afbeeldingen die je niet kunt klikken
function getPictures($conn, $albumLaag, $motherAlbumId){
    $stmt = $conn->prepare('SELECT * FROM images WHERE albumLaag = ? AND motherAlbumId = ?  AND album = 0');
    $stmt->bind_param('ss', $albumLaag, $motherAlbumId);
    $stmt->execute();
    $gallerij = $stmt->get_result();
    while($row = $gallerij->fetch_assoc()){
        echo '
        <div class="lijst">
            <form id="deleteSingle" name="deleteSingle" action="db.php" method="post" enctype="multipart/form-data">
              <img src="'.ltrim($row['locatie'], "/var/www/html/").'">
              <div class="underImage">
                <p class="titel">'.$row["naam"].'</p>
                <input hidden type="text" id="albumId" name="albumId" value="'.$row["albumId"].'">
                <input hidden type="text" id="pictureId" name="pictureId" value="'.$row["pictureId"].'">
                <input type="submit" class="deleteButton" name="deleteSingle" value="DELETE">
              </div>
            </form>
        </div>';
    }
    echo'
        <div class="lijst">
            <img src="fotos/standaard/upload.png" onclick="openForm()"/>
            <p class="titel">Upload image</p>
        </div>';
    return $albumLaag;
}
//spreekt voor zich kijk of de foto bestaat en verwijder hem
function deletePictures($conn, $deleteId){
  //get the id of the picture check if it exists and remove it from the folder
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  $sql = "SELECT locatie FROM images WHERE pictureId = $deleteId";
  $result = mysqli_query($conn, $sql);
  while($row = mysqli_fetch_array($result)) {
    if (file_exists($row['locatie'])) {
      unlink($row['locatie']);
    } else{
      echo "<p>".$row['locatie']."/haha</p>";
    }
  }

}
?>
