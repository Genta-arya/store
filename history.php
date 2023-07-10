<?php
// Ambil member_id dari sesi
$member_id = $_SESSION['member_id'];

// Lakukan koneksi ke database
$host = "localhost";
$user = "root";
$pass = "";
$dbs = "miistore";

$conn = mysqli_connect($host, $user, $pass, $dbs);

// Periksa koneksi
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Query data dari tabel cart
$query = "SELECT * FROM cart WHERE member_id = '$member_id'";
$result = mysqli_query($conn, $query);

// Tampilkan data dari tabel cart
?>
<!DOCTYPE html>
<html>

<head>
    <title>Riwayat Pesanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        h2 {
            text-align: center;
            margin-top: 50px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th {
            background-color: #333;
            color: #fff;
            padding: 10px;
        }

        td {
            padding: 10px;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Riwayat Pesanan</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Pesanan</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Status Pembayaran</th>
                <!-- Tambahkan kolom lainnya sesuai kebutuhan -->
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                if (isset($row['id_pesanan'])) {
                    $id_pesanan = $row['id_pesanan'];
                } else {
                    $id_pesanan = 'N/A';
                }

                if (isset($row['nama'])) {
                    $product_name = $row['nama'];
                } else {
                    $product_name = 'N/A';
                }

                if (isset($row['jumlah'])) {
                    $quantity = $row['jumlah'];
                } else {
                    $quantity = 'N/A';
                }

                if (isset($row['harga'])) {
                    $total = $row['harga'];
                } else {
                    $total = 'N/A';
                }

                // Tambahkan kolom lainnya sesuai kebutuhan
            
                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>$id_pesanan</td>";
                echo "<td>$product_name</td>";
                echo "<td>$quantity</td>";
                echo "<td>$total</td>";
                echo "<td>";
                // Ambil status pembayaran dari API Midtrans
                $order_id = $row['id_pesanan'];
                $serverKey = "U0ItTWlkLXNlcnZlci1CR1lmQTRTQnFrYmJEcUFneWNCYkJxSUI6R2VudGE0NTY="; // Ganti dengan Server Key Midtrans Anda

                $url = "https://api.sandbox.midtrans.com/v2/{$order_id}/status";

                $headers = array(
                    'Accept: application/json',
                    'Authorization: Basic U0ItTWlkLXNlcnZlci1CR1lmQTRTQnFrYmJEcUFneWNCYkJxSUI6R2VudGE0NTY=',
                    'Content-Type: application/json'
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($httpCode == 200) {
                    $responseData = json_decode($response, true);
                    if (isset($responseData['status_code'])) {
                        $transactionStatus = $responseData['status_code'];
                        if ($transactionStatus == '200') {
                            echo "Pembayaran berhasil";
                        } elseif ($transactionStatus == '201') {
                            echo "Pembayaran dalam proses";
                        } elseif ($transactionStatus == '407') {
                            echo "Pembayaran tidak berhasil";
                        } else {
                            echo "Status pembayaran tidak valid";
                        }
                    } else {
                        echo "N/A";
                    }
                } else {
                    echo "Failed to retrieve payment status. HTTP Status Code: " . $httpCode;
                }

                curl_close($ch);
                echo "</td>";
                // Tampilkan kolom lainnya sesuai kebutuhan
                echo "</tr>";

                $no++;
            }
            ?>
        </tbody>
    </table>
</body>

</html>
