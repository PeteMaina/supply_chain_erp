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

// Get product counts
$sql_total_products = "SELECT COUNT(*) as total FROM products";
$result_total_products = $conn->query($sql_total_products);
$total_products = $result_total_products->fetch_assoc()["total"];

$sql_out_of_stock = "SELECT COUNT(*) as total FROM products WHERE quantity = 0";
$result_out_of_stock = $conn->query($sql_out_of_stock);
$out_of_stock = $result_out_of_stock->fetch_assoc()["total"];

$sql_overstocked = "SELECT COUNT(*) as total FROM products WHERE quantity > 100";
$result_overstocked = $conn->query($sql_overstocked);
$overstocked = $result_overstocked->fetch_assoc()["total"];

// Get product value by category
$sql_category_value = "SELECT c.name, SUM(p.price * p.quantity) as total_value 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      GROUP BY c.name";
$result_category_value = $conn->query($sql_category_value);

$categories = [];
$category_values = [];

if ($result_category_value->num_rows > 0) {
    while ($row = $result_category_value->fetch_assoc()) {
        $categories[] = $row["name"];
        $category_values[] = $row["total_value"];
    }
}

// Get top selling products
$sql_top_products = "SELECT p.name, COUNT(oi.id) as total_sold 
                    FROM products p 
                    JOIN order_items oi ON p.id = oi.product_id 
                    GROUP BY p.id 
                    ORDER BY total_sold DESC 
                    LIMIT 5";
$result_top_products = $conn->query($sql_top_products);

$top_products = [];
$top_products_sales = [];

if ($result_top_products && $result_top_products->num_rows > 0) {
    while ($row = $result_top_products->fetch_assoc()) {
        $top_products[] = $row["name"];
        $top_products_sales[] = $row["total_sold"];
    }
} else {
    // Sample data if no sales data exists
    $top_products = ["Product A", "Product B", "Product C", "Product D", "Product E"];
    $top_products_sales = [120, 90, 70, 50, 30];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <style>
    body {
    margin: 0;
    font-family: Arial, sans-serif;
    display: flex;
    height: 100vh;
  
  }
  
  .dashboard-container {
    display: flex;
    width: 100%;
  }
  
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

@media (max-width: 768px) {
  .sidebar {
      width: 200px;
  }
}

a {
  text-decoration: none;
  margin-top: 5px;
  display: flex;
  width: 100%;
  color: white;
}

  
  .main-content {
    flex: 1;
    padding: 20px;
    transition: margin-left 0.3s ease-in-out;
    width: 100%;
    justify-content: center;
    align-items: center;
    text-align: center;
    margin-left: 0;
  }

  .sidebar.active + .main-content {
    margin-left: 250px;
  }

  header h1 {
    margin-bottom: 20px;
    justify-content: center;
    text-align: center;
    align-items: center;
  }
  
  .cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
  }
  
  .card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    flex: 1;
    text-align: center;
    min-width: 200px;
  }
  
  .card h3 {
    margin: 0 0 10px;
  }
  
  .charts {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
  }
  
  .chart {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    flex: 1;
    text-align: center;
    min-width: 300px;
  }
  
  canvas {
    max-width: 100%;
    margin: 0 auto; 
  }

  footer {
    width: 100%;
    background: #0E3D77;
    color: white;
    text-align: center;
    padding: 10px 0;
    position: fixed;
    bottom: 0;
    left: 0;
  }

  footer a {
    color: white;
    text-decoration: none;
  }
  </style>
</head>
<body>
    <span class="menu-icon" id="menuIcon" onclick="toggleSidebar()">☰</span>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li class="active">📊 Dashboard Overview</li>
            <li><a href="inventory.php">📦 Inventory Management</a></li>
            <li><a href="orders.php">🚚 Order and logistics</a></li>
            <li><a href="suppliers.php">📈 Suppliers</a></li>
            <li><a href="#">⚙️ Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <main class="main-content">
      <header>
        <h1>Dashboard</h1>
      </header>

      <section class="cards">
        <div class="card">
          <h3>Count of all products</h3>
          <p><?php echo $total_products; ?> products</p>
        </div>
        <div class="card">
          <h3>Products out of stock</h3>
          <p><?php echo $out_of_stock; ?> products</p>
        </div>
        <div class="card">
          <h3>Products overstocked</h3>
          <p><?php echo $overstocked; ?> products</p>
        </div>
      </section>

      <!-- Donut Charts Section -->
      <section class="charts">
        <div class="chart">
          <h3>Product value by category</h3>
          <canvas id="categoryChart"></canvas>
        </div>
        <div class="chart">
          <h3>Top Selling Products</h3>
          <canvas id="topSellingProductsChart" width="400" height="200"></canvas>
        </div>
      </section>
    </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        const menuIcon = document.getElementById("menuIcon");
        const mainContent = document.querySelector(".main-content");

        sidebar.classList.toggle("active");
        
        // Toggle the menu icon between ☰ and ✖
        if (sidebar.classList.contains("active")) {
            menuIcon.textContent = "✖";
        } else {
            menuIcon.textContent = "☰";
        }
    }

    // Donut Chart: Product Value by Category
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
          data: <?php echo json_encode($category_values); ?>,
          backgroundColor: ['#4BC0C0', '#FF9F40', '#9966FF', '#36A2EB', '#FF6384']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'bottom'
          }
        }
      }
    });

    // Data for Top-Selling Products
    const topSellingProductsData = {
      labels: <?php echo json_encode($top_products); ?>,
      datasets: [{
          label: 'Units Sold',
          data: <?php echo json_encode($top_products_sales); ?>,
          backgroundColor: [
              'rgba(255, 99, 132, 0.7)',
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 206, 86, 0.7)',
              'rgba(75, 192, 192, 0.7)',
              'rgba(153, 102, 255, 0.7)'
          ],
          borderWidth: 1
      }]
    };

    // Config for Top-Selling Products Chart
    const topSellingProductsConfig = {
      type: 'bar',
      data: topSellingProductsData,
      options: {
          responsive: true,
          plugins: {
              legend: {
                  display: false,
              },
          },
          scales: {
              y: {
                  beginAtZero: true
              }
          }
      }
    };

    // Render the Chart
    const topSellingProductsChart = new Chart(
      document.getElementById('topSellingProductsChart'),
      topSellingProductsConfig
    );
  </script>

  <footer><a href="logout.php"><strong>Logout</strong></a></footer>
</body>
</html>

