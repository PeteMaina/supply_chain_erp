<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
   header("location: auth.php");
   exit;
}

// Include database connection
require_once "db_connect.php";

// Get orders for table
$sql_orders = "SELECT o.id, o.order_id, c.name as customer_name, o.status, o.order_date 
              FROM orders o 
              LEFT JOIN customers c ON o.customer_id = c.id 
              ORDER BY o.id DESC";
$result_orders = $conn->query($sql_orders);

// Get tracking data
$sql_tracking = "SELECT t.id, t.tracking_number, t.status, t.update_date, o.order_id 
               FROM tracking t 
               JOIN orders o ON t.order_id = o.id 
               ORDER BY t.update_date DESC";
$result_tracking = $conn->query($sql_tracking);

// Get carriers
$sql_carriers = "SELECT * FROM carriers ORDER BY id DESC";
$result_carriers = $conn->query($sql_carriers);

// Get return requests
$sql_returns = "SELECT r.id, o.order_id, c.name as customer_name, r.reason, r.status 
              FROM returns r 
              JOIN orders o ON r.order_id = o.id 
              LEFT JOIN customers c ON r.customer_id = c.id 
              ORDER BY r.id DESC";
$result_returns = $conn->query($sql_returns);

// Get delivery summaries
$sql_delivery = "SELECT * FROM delivery_summary ORDER BY delivery_date DESC";
$result_delivery = $conn->query($sql_delivery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>Orders Management</title>
 <style>
   /* Global Styles */
   html, body {
   height: 100%;
   display: flex;
   flex-direction: column;
}

.main-content {
   flex: 1; /* Pushes content up and footer down */
}

   * {
     
     box-sizing: border-box;
     margin: 0;
     padding: 0;
   }
   
   body {
     font-family: 'Arial', sans-serif;
     background-color: #eef2f3;
     color: #333;
   }
   
   /* Global Button Styling (applies to all buttons) */
   button {
     background: #1A73E8;
     color: white;
     padding: 8px 12px;
     border: none;
     border-radius: 4px;
     cursor: pointer;
     font-size: 14px;
     box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
     transition: background 0.3s, box-shadow 0.3s;
     display: inline-block;
   }
   
   button:hover {
     background: #1664C1;
     box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
   }
   
   button.active {
     background: #0E3D77;
     box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
   }
   
   /* Top Navigation (Horizontal, right aligned) */
   .top-nav {
     width: 100%;
     padding: 10px;
   }
   
   .top-nav ul {
     list-style: none;
     display: flex;
     justify-content: flex-end;
   }
   
   .top-nav ul li {
     margin-left: 10px;
   }
   
   /* Container for sections */
   .container {
     padding: 20px;
   }
   
   h1,
   h2,
   h3,
   h4 {
     color: #0E3D77;
     margin-bottom: 10px;
   }
   
   /* Tables */
   table {
     width: 100%;
     border-collapse: collapse;
     margin-bottom: 20px;
   }
   
   table,
   th,
   td {
     border: 1px solid #ccc;
   }
   
   th,
   td {
     padding: 10px;
     text-align: left;
   }
   
   /* Forms & Inputs */
   form {
     display: flex;
     flex-direction: column;
     gap: 10px;
     margin-bottom: 20px;
   }
   
   input,
   select,
   textarea {
     padding: 10px;
     border: 1px solid #ccc;
     border-radius: 5px;
   }
   
   /* Summary Boxes */
   .summary {
     display: flex;
     justify-content: space-around;
     flex-wrap: wrap;
   }
   
   .summary-item {
     background: #f0f0f0;
     padding: 20px;
     border-radius: 5px;
     margin: 5px;
     text-align: center;
     flex: 1;
     min-width: 150px;
   }
   
   /* Result Messages */
   .result-message,
   .tracking-result,
   .update-result {
     margin-top: 10px;
     font-weight: bold;
     text-align: center;
   }
   
   /* Sections (all hidden by default) */
   .section {
     display: none;
   }
   
   /* Sidebar */
   .sidebar {
     width: 250px;
     height: 100%;
     background-color: #0E3D77;
     color: white;
     position: fixed;
     left: -250px;
     top: 0;
     transition: left 0.3s ease-in-out;
     padding-top: 60px;
     z-index: 1000;
   }
   
   .sidebar.active {
     left: 0;
   }
   
   .sidebar ul {
     list-style: none;
     padding: 0;
     margin: 0;
   }
   
   .sidebar ul li {
     padding: 15px 20px;
     cursor: pointer;
     border-bottom: 1px solid rgba(255, 255, 255, 0.2);
     transition: background 0.3s;
     text-align: left;
   }
   
   .sidebar ul li:hover {
     background-color: #8EB4E3;
   }
   
   .sidebar ul li.active {
     background-color: #1A73E8;
   }
   
   .menu-icon {
     font-size: 24px;
     cursor: pointer;
     background: #0E3D77;
     color: white;
     padding: 10px 15px;
     border-radius: 5px;
     display: inline-block;
     transition: 0.3s;
     position: fixed;
     top: 15px;
     left: 15px;
     z-index: 1000;
   }
   
   .menu-icon:hover {
     background: #1A73E8;
   }
   
   a {
     text-decoration: none;
     margin-top: 5px;
     display: flex;
     width: 100%;
     color: white;
   }
   
   @media (max-width: 768px) {
     form {
       font-size: 14px;
     }
   }

footer {
   width: 100%;
   background: #0E3D77;
   color: white;
   text-align: center;
   justify-content: center;
   text-align: center;
   display: flex;
   width: 100%;
   padding: 10px 0;
   position: fixed;
   bottom: 0;
   left: 0;
   }

 </style>
 <link rel="shortcut icon" href="icon.png" type="image/x-icon">
</head>
<body>
 <span class="menu-icon" id="menuIcon" onclick="toggleSidebar()">☰</span>
 
 <!-- Sidebar -->
 <div class="sidebar" id="sidebar">
   <ul>
     <li><a href="dashboard.php">📊 Dashboard Overview</a></li>
     <li><a href="inventory.php">📦 Inventory Management</a></li>
     <li class="active"><a href="orders.php">🚚 Order and logistics</a></li>
     <li><a href="suppliers.php">📈 Suppliers</a></li>
     <li><a href="#">⚙️ Settings</a></li>
   </ul>
 </div>
 
 <!-- Top Navigation -->
 <nav class="top-nav">
   <ul>
     <li><button onclick="showSection('orderProcessing', this)">Order Processing</button></li>
     <li><button onclick="showSection('newOrder', this)">New Order</button></li>
     <li><button onclick="showSection('trackingStatus', this)">Tracking &amp; Status</button></li>
     <li><button onclick="showSection('carrierDetails', this)">Carrier Details</button></li>
     <li><button onclick="showSection('deliverySummary', this)">Delivery Summary</button></li>
     <li><button onclick="showSection('realTimeTracking', this)">Real-Time Tracking</button></li>
     <li><button onclick="showSection('returnExchange', this)">Return &amp; Exchange</button></li>
   </ul>
 </nav>
 
 <!-- Main Content Container -->
 <div class="container">
 
   
   <!-- Order Processing and Fulfillment Section -->
   <div id="orderProcessing" class="section">
     <h2>Order Processing and Fulfillment</h2>
     <section class="current-orders">
       <h3>Current Orders</h3>
       <table>
         <thead>
           <tr>
             <th>Order ID</th>
             <th>Customer Name</th>
             <th>Status</th>
             <th>Actions</th>
           </tr>
         </thead>
         <tbody>
           <?php
           if ($result_orders && $result_orders->num_rows > 0) {
               // Reset the pointer to the beginning of the result set
               $result_orders->data_seek(0);
               while ($row = $result_orders->fetch_assoc()) {
                   echo '<tr>';
                   echo '<td>' . $row["order_id"] . '</td>';
                   echo '<td>' . $row["customer_name"] . '</td>';
                   echo '<td>' . $row["status"] . '</td>';
                   echo '<td>';
                   if ($row["status"] == "Processing") {
                       echo '<button onclick="orderProcessingProcess(\'' . $row["order_id"] . '\')">Process</button>';
                   } elseif ($row["status"] == "Shipped") {
                       echo '<button onclick="orderProcessingProcess(\'' . $row["order_id"] . '\')">Update Status</button>';
                   } else {
                       echo '<button onclick="orderProcessingProcess(\'' . $row["order_id"] . '\')">View Details</button>';
                   }
                   echo '</td>';
                   echo '</tr>';
               }
           } else {
               echo '<tr><td colspan="4" class="text-center">No orders found</td></tr>';
           }
           ?>
         </tbody>
       </table>
     </section>
     <section class="process-order">
       <h3>Process a New Order</h3>
       <form id="orderProcessingForm" action="process_order.php" method="post">
         <label for="orderProcessingId">Order ID:</label>
         <input type="text" id="orderProcessingId" name="order_id" required />
         <label for="orderProcessingCustomer">Customer Name:</label>
         <input type="text" id="orderProcessingCustomer" name="customer_name" required />
         <label for="orderProcessingStatus">Order Status:</label>
         <select id="orderProcessingStatus" name="status">
           <option value="Processing">Processing</option>
           <option value="Shipped">Shipped</option>
           <option value="Delivered">Delivered</option>
         </select>
         <button type="submit">Submit Order</button>
       </form>
     </section>
     <section class="fulfillment-summary">
       <h3>Fulfillment Summary</h3>
       <div class="summary">
         <?php
         // Count orders by status
         $sql_processing = "SELECT COUNT(*) as count FROM orders WHERE status = 'Processing'";
         $result_processing = $conn->query($sql_processing);
         $processing_count = $result_processing->fetch_assoc()["count"];
         
         $sql_shipped = "SELECT COUNT(*) as count FROM orders WHERE status = 'Shipped'";
         $result_shipped = $conn->query($sql_shipped);
         $shipped_count = $result_shipped->fetch_assoc()["count"];
         
         $sql_delivered = "SELECT COUNT(*) as count FROM orders WHERE status = 'Delivered'";
         $result_delivered = $conn->query($sql_delivered);
         $delivered_count = $result_delivered->fetch_assoc()["count"];
         
         $total_orders = $processing_count + $shipped_count + $delivered_count;
         ?>
         <div class="summary-item">
           <h4>Total Orders</h4>
           <p><?php echo $total_orders; ?></p>
         </div>
         <div class="summary-item">
           <h4>Orders Processed</h4>
           <p><?php echo $shipped_count + $delivered_count; ?></p>
         </div>
         <div class="summary-item">
           <h4>Pending Orders</h4>
           <p><?php echo $processing_count; ?></p>
         </div>
       </div>
     </section>
   </div>
   
   <!-- New Order Section -->
   <div id="newOrder" class="section">
     <h2>Orders Management</h2>
     <section class="current-orders">
       <h3>Current Orders</h3>
       <table>
         <thead>
           <tr>
             <th>Order ID</th>
             <th>Customer Name</th>
             <th>Status</th>
             <th>Order Date</th>
             <th>Actions</th>
           </tr>
         </thead>
         <tbody>
           <?php
           if ($result_orders && $result_orders->num_rows > 0) {
               // Reset the pointer to the beginning of the result set
               $result_orders->data_seek(0);
               while ($row = $result_orders->fetch_assoc()) {
                   echo '<tr>';
                   echo '<td>' . $row["order_id"] . '</td>';
                   echo '<td>' . $row["customer_name"] . '</td>';
                   echo '<td>' . $row["status"] . '</td>';
                   echo '<td>' . $row["order_date"] . '</td>';
                   echo '<td><button onclick="newOrderView(\'' . $row["order_id"] . '\')">View</button></td>';
                   echo '</tr>';
               }
           } else {
               echo '<tr><td colspan="5" class="text-center">No orders found</td></tr>';
           }
           ?>
         </tbody>
       </table>
     </section>
     <section class="add-order">
       <h3>Add New Order</h3>
       <form id="newOrderForm" action="add_order.php" method="post">
         <label for="newOrderId">Order ID:</label>
         <input type="text" id="newOrderId" name="order_id" required />
         <label for="newCustomerName">Customer Name:</label>
         <input type="text" id="newCustomerName" name="customer_name" required />
         <label for="newOrderStatus">Order Status:</label>
         <select id="newOrderStatus" name="status">
           <option value="Processing">Processing</option>
           <option value="Shipped">Shipped</option>
           <option value="Delivered">Delivered</option>
         </select>
         <label for="orderDate">Order Date:</label>
         <input type="date" id="orderDate" name="order_date" required />
         <button type="submit">Submit Order</button>
       </form>
     </section>
     <section class="order-summary">
       <h3>Order Summary</h3>
       <div class="summary">
         <div class="summary-item">
           <h4>Total Orders</h4>
           <p><?php echo $total_orders; ?></p>
         </div>
         <div class="summary-item">
           <h4>Orders Processing</h4>
           <p><?php echo $processing_count; ?></p>
         </div>
         <div class="summary-item">
           <h4>Orders Shipped</h4>
           <p><?php echo $shipped_count; ?></p>
         </div>
         <div class="summary-item">
           <h4>Orders Delivered</h4>
           <p><?php echo $delivered_count; ?></p>
         </div>
       </div>
     </section>
   </div>
   
   <!-- Tracking & Status Update Section -->
   <div id="trackingStatus" class="section">
     <h2>Tracking and Status Update</h2>
     <section class="track-order">
       <h3>Track Your Order</h3>
       <form id="trackForm" action="track_order.php" method="post">
         <label for="trackingNumberTS">Tracking Number:</label>
         <input type="text" id="trackingNumberTS" name="tracking_number" required />
         <button type="submit">Track Order</button>
       </form>
       <div id="trackingResultTS" class="tracking-result"></div>
     </section>
     <section class="update-status">
       <h3>Update Order Status</h3>
       <form id="updateForm" action="update_order_status.php" method="post">
         <label for="orderIdUS">Order ID:</label>
         <input type="text" id="orderIdUS" name="order_id" required />
         <label for="orderStatusUS">New Status:</label>
         <select id="orderStatusUS" name="status">
           <option value="Processing">Processing</option>
           <option value="Shipped">Shipped</option>
           <option value="Delivered">Delivered</option>
           <option value="Returned">Returned</option>
         </select>
         <button type="submit">Update Status</button>
       </form>
       <div id="updateResultUS" class="update-result"></div>
     </section>
     <section class="tracking-history">
       <h3>Tracking History</h3>
       <table>
         <thead>
           <tr>
             <th>Order ID</th>
             <th>Status</th>
             <th>Date</th>
           </tr>
         </thead>
         <tbody>
           <?php
           if ($result_tracking && $result_tracking->num_rows > 0) {
               while ($row = $result_tracking->fetch_assoc()) {
                   echo '<tr>';
                   echo '<td>' . $row["order_id"] . '</td>';
                   echo '<td>' . $row["status"] . '</td>';
                   echo '<td>' . $row["update_date"] . '</td>';
                   echo '</tr>';
               }
           } else {
               echo '<tr><td colspan="3" class="text-center">No tracking history found</td></tr>';
           }
           ?>
         </tbody>
       </table>
     </section>
   </div>
   
   <!-- Carrier Details Section -->
   <div id="carrierDetails" class="section">
     <h2>Carrier Details</h2>
     <section class="carriers-list">
       <h3>Carriers List</h3>
       <table>
         <thead>
           <tr>
             <th>Name</th>
             <th>Contact Number</th>
             <th>Email</th>
             <th>Status</th>
           </tr>
         </thead>
         <tbody>
           <?php
           if ($result_carriers && $result_carriers->num_rows > 0) {
               while ($row = $result_carriers->fetch_assoc()) {
                   echo '<tr>';
                   echo '<td>' . $row["name"] . '</td>';
                   echo '<td>' . $row["contact_number"] . '</td>';
                   echo '<td>' . $row["email"] . '</td>';
                   echo '<td>' . $row["status"] . '</td>';
                   echo '</tr>';
               }
           } else {
               echo '<tr><td colspan="4" class="text-center">No carriers found</td></tr>';
           }
           ?>
         </tbody>
       </table>
     </section>
     <section class="add-carrier">
       <h3>Add New Carrier</h3>
       <form id="addCarrierForm">
         <label for="carrierName">Carrier Name:</label>
         <input type="text" id="carrierName" name="name" required />
         <label for="carrierContact">Contact Number:</label>
         <input type="text" id="carrierContact" name="contact_number" required />
         <label for="carrierEmail">Email:</label>
         <input type="email" id="carrierEmail" name="email" required />
         <label for="carrierStatus">Status:</label>
         <select id="carrierStatus" name="status">
           <option value="Active">Active</option>
           <option value="Inactive">Inactive</option>
         </select>
         <button type="button" onclick="addCarrier()">Add Carrier</button>
       </form>
     </section>
   </div>
   
   <!-- Delivery Summary Section -->
   <div id="deliverySummary" class="section">
     <h2>Delivery Summary</h2>
     <section class="delivery-stats">
       <h3>Delivery Statistics</h3>
       <table>
         <thead>
           <tr>
             <th>Date</th>
             <th>Total Deliveries</th>
             <th>Successful</th>
             <th>Failed</th>
           </tr>
         </thead>
         <tbody>
           <?php
           if ($result_delivery && $result_delivery->num_rows > 0) {
               while ($row = $result_delivery->fetch_assoc()) {
                   echo '<tr>';
                   echo '<td>' . $row["delivery_date"] . '</td>';
                   echo '<td>' . $row["total_deliveries"] . '</td>';
                   echo '<td>' . $row["successful_deliveries"] . '</td>';
                   echo '<td>' . $row["failed_deliveries"] . '</td>';
                   echo '</tr>';
               }
           } else {
               echo '<tr><td colspan="4" class="text-center">No delivery summaries found</td></tr>';
           }
           ?>
         </tbody>
       </table>
     </section>
     <section class="add-summary">
       <h3>Add Delivery Summary</h3>
       <form id="addSummaryForm">
         <label for="deliveryDate">Delivery Date:</label>
         <input type="date" id="deliveryDate" name="delivery_date" required />
         <label for="totalDeliveries">Total Deliveries:</label>
         <input type="number" id="totalDeliveries" name="total_deliveries" required />
         <label for="successfulDeliveries">Successful Deliveries:</label>
         <input type="number" id="successfulDeliveries" name="successful_deliveries" required />
         <label for="failedDeliveries">Failed Deliveries:</label>
         <input type="number" id="failedDeliveries" name="failed_deliveries" required />
         <button type="button" onclick="addDeliverySummary()">Add Summary</button>
       </form>
     </section>
   </div>
   
   <!-- Real-Time Tracking Section -->
   <div id="realTimeTracking" class="section">
     <h2>Real-Time Tracking</h2>
     <section class="tracking-map">
       <h3>Tracking Map</h3>
       <div id="map" style="height: 400px; background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
         <p>Map visualization would be displayed here</p>
       </div>
     </section>
     <section class="track-order">
       <h3>Track Order in Real-Time</h3>
       <form id="realTimeTrackForm">
         <label for="trackingNumberRT">Tracking Number:</label>
         <input type="text" id="trackingNumberRT" name="tracking_number" required />
         <button type="button" onclick="trackRealTime()">Track Now</button>
       </form>
       <div id="realTimeResult" class="tracking-result"></div>
     </section>
   </div>
   
   <!-- Return & Exchange Section -->
   <div id="returnExchange" class="section">
     <h2>Return and Exchange Management</h2>
     <section class="returns-list">
       <h3>Return Requests</h3>
       <table>
         <thead>
           <tr>
             <th>Order ID</th>
             <th>Customer</th>
             <th>Reason</th>
             <th>Status</th>
           </tr>
         </thead>
         <tbody>
           <?php
           if ($result_returns && $result_returns->num_rows > 0) {
               while ($row = $result_returns->fetch_assoc()) {
                   echo '<tr>';
                   echo '<td>' . $row["order_id"] . '</td>';
                   echo '<td>' . $row["customer_name"] . '</td>';
                   echo '<td>' . $row["reason"] . '</td>';
                   echo '<td>' . $row["status"] . '</td>';
                   echo '</tr>';
               }
           } else {
               echo '<tr><td colspan="4" class="text-center">No return requests found</td></tr>';
           }
           ?>
         </tbody>
       </table>
     </section>
     <section class="process-return">
       <h3>Process Return/Exchange</h3>
       <form id="returnForm" action="process_return.php" method="post">
         <label for="returnOrderId">Order ID:</label>
         <input type="text" id="returnOrderId" name="order_id" required />
         <label for="returnCustomer">Customer Name:</label>
         <input type="text" id="returnCustomer" name="customer_name" required />
         <label for="returnReason">Reason:</label>
         <select id="returnReason" name="reason">
           <option value="Damaged">Damaged</option>
           <option value="Wrong Item">Wrong Item</option>
           <option value="Not as Described">Not as Described</option>
           <option value="Other">Other</option>
         </select>
         <label for="returnComments">Comments:</label>
         <textarea id="returnComments" name="comments" rows="3"></textarea>
         <label for="returnType">Type:</label>
         <select id="returnType" name="type">
           <option value="Return">Return</option>
           <option value="Exchange">Exchange</option>
         </select>
         <div id="exchangeFields" style="display: none;">
           <label for="newItem">New Item:</label>
           <input type="text" id="newItem" name="new_item" />
         </div>
         <button type="submit">Process</button>
       </form>
     </section>
   </div>
 </div>

 <script>
   // Show the first section by default
   document.addEventListener('DOMContentLoaded', function() {
     showSection('orderProcessing', document.querySelector('.top-nav button'));
   });

   // Function to show a section and hide others
   function showSection(sectionId, button) {
     // Hide all sections
     const sections = document.querySelectorAll('.section');
     sections.forEach(section => {
       section.style.display = 'none';
     });

     // Show the selected section
     document.getElementById(sectionId).style.display = 'block';

     // Update active button
     const buttons = document.querySelectorAll('.top-nav button');
     buttons.forEach(btn => {
       btn.classList.remove('active');
     });
     button.classList.add('active');
   }

   // Toggle sidebar
   function toggleSidebar() {
     const sidebar = document.getElementById('sidebar');
     sidebar.classList.toggle('active');
   }

   // Show/hide exchange fields based on return type
   document.getElementById('returnType').addEventListener('change', function() {
     const exchangeFields = document.getElementById('exchangeFields');
     if (this.value === 'Exchange') {
       exchangeFields.style.display = 'block';
     } else {
       exchangeFields.style.display = 'none';
     }
   });

   // Placeholder functions for buttons
   function orderProcessingProcess(orderId) {
     alert('Processing order: ' + orderId);
   }

   function newOrderView(orderId) {
     alert('Viewing order: ' + orderId);
   }

   function addCarrier() {
     alert('Carrier added successfully!');
   }

   function addDeliverySummary() {
     alert('Delivery summary added successfully!');
   }

   function trackRealTime() {
     const trackingNumber = document.getElementById('trackingNumberRT').value;
     document.getElementById('realTimeResult').innerHTML = 
       '<p>Tracking information for ' + trackingNumber + ':</p>' +
       '<p>Status: In Transit</p>' +
       '<p>Location: Distribution Center</p>' +
       '<p>Last Update: ' + new Date().toLocaleString() + '</p>';
   }

   // Check for success message in URL
   document.addEventListener('DOMContentLoaded', function() {
     const urlParams = new URLSearchParams(window.location.search);
     if (urlParams.has('success')) {
       const successType = urlParams.get('success');
       if (successType === 'return') {
         alert('Return processed successfully!');
       } else if (successType === 'exchange') {
         alert('Exchange processed successfully!');
       }
     }
   });
 </script>

 <footer><a href="logout.php"><strong>Logout</strong></a></footer>
</body>
</html>

