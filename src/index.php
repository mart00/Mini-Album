<?php require_once('db.php');
//kijk of er een id is meegegeven (of er op een album gedrukt is)
    if (isset($_GET['id'])){
      $albumLaag = $_GET['id'];
      $motherAlbumId = $_GET['id'];
      $albumId = $_GET['id'];
    }else{
      $albumLaag = 0;
      $motherAlbumId = 0;
      $albumId = 0;
    }
?>
<!DOCTYPE html>
<html>
<head>
 <title>I dunno</title>
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
 <script>
 //kijk of er op een albumgeklikt word en als dat gebeurt geef albumId mee
     $('#changeAlbum').click(function() {
       $.ajax({
         type: "POST",
         url: "db.php",
         data: { change: <?php echo json_encode($row["albumId"]); ?> }
       });
     });
 </script>
 <script>
 //openen / sluiten van het upload form
 //werken alleen als de scripts appart zijn om een of andere reden
   function openForm() {
     document.getElementById("fotoForm").style.display = "block";
   }

   function closeForm() {
     document.getElementById("fotoForm").style.display = "none";
   }
   window.onclick = function (event) {
       if (event.target.className === "fotoForm") {
         event.target.style.display = "none";
       }
   };
 </script>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
      <!-- <button type="button" id="please" onclick="myAjax()">Delete</button> -->
        <section class="gallery">
            <div class="modal-content">
                <div class="contact-form">
                    <form id="fotoForm" class="fotoForm" action="upload.php" method="post" enctype="multipart/form-data">
                        <h2>Select an image to upload:</h2>
                        <input type="hidden" id="albumLaag" name="albumLaag" value="<?=$albumLaag;?>">
                        <input type="hidden" id="albumId" name="albumId" value="<?=$albumId;?>">
                        <input type="text" id="imageName" name="imageName" value="fotonaam">
                        <label class="container">Is this an Album?
                            <input type="hidden" name="album" value="0">
                            <input type="checkbox" name="album" value="1">
                            <span class="checkmark"></span>
                        </label>
                        <input type="file" name="fileToUpload" id="fileToUpload">
                        <input type="submit" value="Upload Image" name="submit">
                    </form>
                </div>
            </div>
        </section>

    <div class="album">
      <?php
      //roep de database op en geef de albums / afbeeldingen weer
        $conn;
        getGallery($conn,$albumLaag, $motherAlbumId);
        getPictures($conn,$albumLaag, $motherAlbumId);
        // deleteAll($conn);
      ?>
    </div>
  </main>
</body>
</html>
