const express = require("express");
const app = express();
const port = 3003;
const API = "192.168.1.4";
const db = require("./koneksi");
const bodyParser = require("body-parser");
const crypto = require("crypto");
const midtransClient = require("midtrans-client");
const cors = require("cors");

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
app.use(cors());

app.listen(port, API, () => {
    console.log(`Example app listening on port ${API}:${port}`);
  });
  



const snap = new midtransClient.Snap({
    isProduction: false,
    serverKey: "SB-Mid-server-BGYfA4SBqkbbDqAgycBbBqIB",
    clientKey: "SB-Mid-client-LAESY4DvSHanXr5C",
  });
  
  app.post("/charge", async (req, res) => {
    const totalAmount = parseFloat(req.body.totalAmount);
    const product_id = req.body.product_id;
    const item_name = req.body.item_name;
    const color = req.body.color;
    const size = req.body.size;
    const quantity = req.body.quantity;
    const pemesan = req.body.fullname;
    const id_pemesan = req.body.member_id;
    const email = req.body.email;
    console.log(email)
  
    const order_id = `ORDER_${Math.round(Math.random() * 100000)}`; // Generate order_id
  
    const insertQuery =
      "INSERT INTO cart (id_pesanan, harga, kode, nama, color, size, jumlah, pemesan, member_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    const values = [order_id, totalAmount, product_id, item_name, color, size, quantity, pemesan, id_pemesan];
  
    db.query(insertQuery, values, async (error, results) => {
      if (error) {
        console.error("Failed to insert data into cart table:", error);
        res.status(500).json({ error: "Failed to process transaction" });
      } else {
        console.log("Transaction data inserted into cart table");
  
        const transactionDetails = {
          order_id: order_id, // Gunakan order_id yang telah digenerate
          gross_amount: totalAmount,
          email : email,
        };
  
        const enabledPayments = ["credit_card", "cimb_clicks", "bca_klikbca"];
  
        const creditCardOptions = {
          save_card: false,
          secure: false,
        };
  
        const transaction = {
          transaction_details: transactionDetails,
          enabled_payments: enabledPayments,
          credit_card: creditCardOptions,
        };
  
        try {
          const transactionToken = await snap.createTransaction(transaction);
          const paymentToken = transactionToken.token;
          console.log("Payment token:", paymentToken);
  
          const paymentData = {
            payment_type: "gopay",
            transaction_details: transactionDetails,
            customer_details: {
                email : email,
            },
          };
  
          const paymentResponse = await snap.createTransaction(paymentData);
          const redirectUrl = paymentResponse.redirect_url;
  
          console.log("Order placed successfully");
          res.status(200).json({ redirectUrl });
        } catch (error) {
          console.error("Failed to create transaction:", error);
          res.status(500).json({ error: "Failed to create transaction" });
        }
      }
    });
  });

