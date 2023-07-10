<div class="shopping">
	<div class="container">
		<div class="col-lg-12">
			<h2 class="text-center text1">Keranjang Belanja</h2>
		</div>
		<table class="timetable_sub">
			<thead>
				<tr>
					<th>Produk</th>
					<th>Jumlah</th>
					<th>Diskon</th>
					<th>Harga</th>
					<th>Total</th>
					<th>Hapus</th>
				</tr>
			</thead>
			<?php
			if (!empty($_SESSION["cart"])) {
				$total = 0;
				foreach ($_SESSION["cart"] as $keys => $values) {
					$totalDisc = $values['price'] - ($values['price'] * $values['disc'] / 100);
					$subtotal = $values['qty'] * $totalDisc;
					$total += $subtotal;
					?>
					<tr>
						<td align="center">
							<div class="table-column-left">
								<img src="../miiadmin/img/<?php echo $values['item_img']; ?>" class="img-small">
							</div>
							<div class="table-column-right">
								Kode :
								<?php echo $values['product_id']; ?><br />
								Nama :
								<?php echo $values['item_name']; ?><br />
								Color :
								<?php echo $values['color']; ?><br />
								Size :
								<?php echo $values['size']; ?><br />

							</div>
						</td>
						<td align="center">
							<?php echo $values['qty']; ?>
						</td>
						<td align="center">
							<?php echo $values['disc']; ?>%
						</td>
						<td align="center">
							<?php echo 'Rp ' . number_format($values['price'], 0, ".", "."); ?>
						</td>
						<td align="center">
							<?php echo 'Rp ' . number_format($subtotal, 0, ".", "."); ?>
						</td>
						<td align="center"><a
								href="../index.php?p=cart&item=<?php echo $values['product_id']; ?>&clr=<?php echo $values['color']; ?>&sz=<?php echo $values['size']; ?>"><i
									class="fa fa-times-circle-o"></i></a></td>
					</tr>
					<?php
				}
			}
			?>
		</table>
		<?php
		if (isset($_GET['item'])) {
			foreach ($_SESSION["cart"] as $keys => $values) {
				if ($values['product_id'] == $_GET['item'] && $values['color'] == $_GET['clr'] && $values['size'] == $_GET['sz']) {
					unset($_SESSION['cart'][$keys]);
				}
			}
			echo "<script>document.location = '../index.php?p=cart'; </script>";
		}
		?>
		<div class="shopping-left">
			<div class="shopping-left-basket">
				<ul>
					<li class="total">Total semua: <span>
							<?php echo 'Rp ' . number_format($total, 2, ",", "."); ?>
						</span></li>
					<li class="user">Pemesan: <span>
							<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : (isset($_COOKIE['email']) ? $_COOKIE['email'] : ''); ?>
						</span></li>
				</ul>
			</div>
			<div class="shopping">
				<div class="container">
					<div class="col-lg-12">
						<h2 class="text-center text1">Keranjang Belanja</h2>
					</div>
					<table class="timetable_sub">
						<!-- Tabel keranjang belanja -->
					</table>

					<div class="shopping-right-basket">

						<a class="btn-continue" href="#" onclick="clearCart()">Hapus Semua</a>
						<a class="btn-continue" href="../history.php">Lihat Riwayat Pesanan</a>
						<button class="btn-continue " onclick="payWithMidtrans()">Bayar</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	function payWithMidtrans() {
		var totalAmount = parseFloat('<?php echo $total; ?>');
		var product_id = <?php echo $values['product_id']; ?>;
		var item_name = '<?php echo $values['item_name']; ?>';
		var color = '<?php echo $values['color']; ?>';
		var size = '<?php echo $values['size']; ?>';
		var quantity = <?php echo $values['qty']; ?>;
		var fullname = '<?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : (isset($_COOKIE['fullname']) ? $_COOKIE['fullname'] : ''); ?>';
		var member_id = '<?php echo isset($_SESSION['member_id']) ? $_SESSION['member_id'] : (isset($_COOKIE['member_id']) ? $_COOKIE['member_id'] : ''); ?>';
		var email = '<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : (isset($_COOKIE['email']) ? $_COOKIE['email'] : ''); ?>'
		if (isNaN(totalAmount)) {
			console.error('Total amount is not a valid number');
			return;
		}

		var paymentData = {
			totalAmount: totalAmount,
			product_id: product_id,
			item_name: item_name,
			color: color,
			size: size,
			quantity: quantity,
			fullname: fullname,
			member_id: member_id,
			email: email
		};

		fetch('http://192.168.1.4:3003/charge', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(paymentData)
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Failed to obtain payment URL');
				}
				return response.json();
			})
			.then(function (responseData) {
				var paymentURL = responseData.redirectUrl; // Menggunakan "redirectUrl" sebagai kunci
				if (paymentURL) {
					// Buka URL pembayaran di tab baru
					window.open(paymentURL, '_blank');

					// Redirect tab saat ini ke halaman riwayat pesanan
					window.location.href = '../history.php';
				} else {
					console.error('Failed to obtain payment URL');
				}
			})
			.catch(function (error) {
				console.error(error);
			});
	}
	function clearCart() {
		window.location.href = "../index.php?p=cart&act=clear";
	}

</script>