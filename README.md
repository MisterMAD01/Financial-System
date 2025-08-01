# ระบบจัดการการเงิน

โปรเจคนี้เป็นเว็บระบบจัดการการเงิน พัฒนาด้วย **PHP** และ **Bootstrap** โดยเก็บข้อมูลธุรกรรมไว้ใน **Session**

---

## ฟีเจอร์หลัก

### ระบบล็อกอิน-ออกจากระบบ
- ตรวจสอบอีเมลและรหัสผ่านกับฐานข้อมูล SQLite
- หากล็อกอินสำเร็จ จะสร้าง `session['user_id']`และ'$_SESSION['user_email']' เพื่อใช้งานระบบ
- ระบบตรวจสอบสิทธิ์ก่อนเข้าหน้าอื่น หากไม่มี session จะถูกรีไดเร็กไปหน้า login
- สามารถออกจากระบบโดยการลบ session ทั้งหมด (`session_unset()` และ `session_destroy()`)

### บันทึกธุรกรรม (transaction.php)
- กรอกจำนวนเงิน และเลือกทำรายการ “ฝาก” หรือ “ถอน”
- ระบบตรวจสอบยอดเงินคงเหลือก่อนถอน (ไม่อนุญาตให้ถอนเกินยอด)
- รายการธุรกรรมจะถูกเก็บใน `$_SESSION['transactions']` เป็นอาเรย์ที่เก็บข้อมูลวันที่, ประเภทธุรกรรม, จำนวนเงิน และอีเมลผู้ทำรายการ

### แสดงประวัติธุรกรรม (history.php)
- แสดงตารางรายการธุรกรรมทั้งหมด จาก `$_SESSION['transactions']`
- วันที่และเวลาจะแสดงในรูปแบบปีพุทธศักราช (เพิ่ม 543 ปี)
- ประเภทธุรกรรมแสดงเป็น “ฝาก” หรือ “ถอน” พร้อมสีเขียว-แดงแยกประเภท
- มีปุ่มแก้ไขและลบในแต่ละรายการ
- ใช้ **Bootstrap Modal** สำหรับการแก้ไขและลบ พร้อมส่งข้อมูลผ่าน POST ไปยัง `update_transaction.php` และ `delete_transaction.php`

### แก้ไขธุรกรรม (update_transaction.php)
- รับข้อมูล POST ได้แก่ ดัชนีธุรกรรม, ประเภท, และจำนวนเงิน
- ตรวจสอบความถูกต้องของข้อมูล เช่น ดัชนีต้องมีอยู่จริง, ประเภทต้องเป็น ‘deposit’ หรือ ‘withdraw’, จำนวนเงินต้องมากกว่า 0
- อัปเดตข้อมูลใน `$_SESSION['transactions']`

### ลบธุรกรรม (delete_transaction.php)
- รับข้อมูล POST ดัชนีธุรกรรม
- ตรวจสอบดัชนีว่ามีอยู่ใน session หรือไม่
- ลบรายการธุรกรรมออกจาก `$_SESSION['transactions']`

### หน้า index.php (หรือไฟล์ที่ตรวจสอบ session)
- หากพบว่ามี session `user_id` อยู่ ให้รีไดเร็กไปหน้า `transaction.php`
- หากไม่มี session `user_id` ให้รีไดเร็กไปหน้า `login.php`

### ฐานข้อมูล database.db
- ใช้ SQLite สำหรับตรวจสอบการล็อกอิน
- มีตาราง users เก็บอีเมลและรหัสผ่าน
- CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);
- INSERT INTO users (email, password)
VALUES ('6560506027@pnu.ac.th', '1234');

---

## ข้อมูลล็อกอินทดสอบ

- Username: `6560506027@pnu.ac.th`  
- Password: `1234`

---

## หมายเหตุ

- ธุรกรรมทั้งหมดถูกเก็บใน session ซึ่งเป็นการเก็บข้อมูลชั่วคราว (ถ้าปิดเบราว์เซอร์หรือ session หมดอายุ ข้อมูลจะหายไป)
- ใช้ Bootstrap 5.3 
- รูปแบบวันที่ในประวัติ ใช้ฟังก์ชันแปลงเป็นปีพุทธศักราช
