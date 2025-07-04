<?php 



    $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");

     $query2 = "SELECT * FROM `users`";
        $res2 = $db->query($query2);
        
        
        
   
        // Fetch and display data
        while ($row = $res2->fetch_assoc()) {
              $url2 = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendMessage?chat_id=".$row['id']."&text=".$_GET['text'];

// Send the message via GET request
               file_get_contents($url2);
        }

    


?>