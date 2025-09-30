<?php
$conn=new mysqli("localhost","root","","taho");
$id=(int)$_GET['id'];
$res=$conn->query("SELECT product_name,price,quantity FROM orders_items WHERE order_id=$id");
if($res && $res->num_rows>0){
  echo "<table class='table'><thead><tr><th>Product</th><th>Price</th><th>Qty</th></tr></thead><tbody>";
  while($row=$res->fetch_assoc()){
    echo "<tr><td>".htmlspecialchars($row['product_name'])."</td>
              <td>₱".number_format($row['price'],2)."</td>
              <td>".$row['quantity']."</td></tr>";
  }
  echo "</tbody></table>";
}else{
  echo "No items found.";
}