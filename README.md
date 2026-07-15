# Ember & Oak — Full-Stack Restaurant Website

PHP + MySQL + AWS S3 (for menu images). Public site (menu, reservations, contact) +
admin dashboard (manage menu items with image upload to S3, reservations, messages).

## Project structure

```
restaurant-project/
├── public/            # the public website (this is your Apache/Nginx web root)
│   ├── index.php, menu.php, about.php, contact.php, reserve.php
│   ├── partials/       (header/footer)
│   └── assets/         (css, js, placeholder images)
├── admin/              # admin dashboard (protect or put behind auth — already login-gated)
├── includes/           # db.php, s3.php, auth.php, env.php  (shared, outside web root ideally)
├── sql/schema.sql       # run this once on your database
├── composer.json         # AWS SDK for PHP
└── .env.example
```

## 1. Local setup

```bash
composer install
cp .env.example .env
# edit .env with your DB + S3 values
```

Default admin login (created by schema.sql): `admin` / `Admin@123` — **change this immediately** after first login (or regenerate the hash with `php -r "echo password_hash('newpass', PASSWORD_BCRYPT);"` and update the `admins` table).

## 2. AWS resources you need

| Resource | Purpose |
|---|---|
| **EC2 instance** (e.g. t3.micro/small, Amazon Linux 2023 or Ubuntu) | Runs Apache/Nginx + PHP |
| **RDS MySQL** (e.g. db.t3.micro) | Database — or MySQL on the same EC2 box if you want to keep costs at $0 during testing |
| **S3 bucket** | Stores menu item images |
| **IAM Role** attached to the EC2 instance | Lets PHP talk to S3 without hardcoding keys |
| *(optional)* CloudFront + ACM cert | HTTPS + faster image delivery |
| *(optional)* Application Load Balancer | If you want a health-check/HA setup later |

## 3. S3 bucket setup

1. Create the bucket (e.g. `emberoak-menu-images`), same region as your EC2.
2. Keep "Block all public access" **off** only if you want direct public image URLs (simplest for a menu gallery). Alternative: keep it fully private and serve images through CloudFront with Origin Access Control — more secure, slightly more setup.
3. If serving directly from S3, attach this bucket policy (replace bucket name):

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadMenuImages",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::emberoak-menu-images/menu/*"
    }
  ]
}
```

4. Set `S3_BUCKET` and `AWS_REGION` in `.env`.

## 4. IAM Role (recommended over access keys)

1. IAM → Roles → Create role → AWS service → EC2.
2. Attach a policy scoped to just this bucket (least privilege), e.g.:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": ["s3:PutObject", "s3:DeleteObject", "s3:GetObject"],
      "Resource": "arn:aws:s3:::emberoak-menu-images/menu/*"
    }
  ]
}
```

3. Attach the role to your EC2 instance (Actions → Security → Modify IAM role).
4. Leave `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` **empty** in `.env` on EC2 — the SDK auto-detects the instance role. Only fill those in for local development off AWS.

## 5. RDS setup

1. Create a MySQL RDS instance. Note the endpoint, username, password.
2. Security group: allow inbound MySQL (3306) **only** from your EC2 instance's security group, not `0.0.0.0/0`.
3. Import the schema:
```bash
mysql -h <RDS_ENDPOINT> -u admin -p < sql/schema.sql
```
4. Fill `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` in `.env`.

## 6. EC2 deployment steps

```bash
# On the instance (Amazon Linux 2023 example)
sudo dnf install -y php php-mysqlnd php-mbstring php-xml httpd
sudo systemctl enable --now httpd

# Install composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Deploy your code (git clone, scp, or CodeDeploy — pick one)
cd /var/www
sudo git clone <your-repo-url> restaurant-project
cd restaurant-project
composer install --no-dev --optimize-autoloader
cp .env.example .env   # then edit with real RDS/S3 values

# Point Apache's DocumentRoot at the public/ folder specifically —
# includes/ and admin-config should NOT be web-accessible from outside public/
```

Example Apache vhost (`/etc/httpd/conf.d/restaurant.conf`):
```apache
<VirtualHost *:80>
    ServerName your-domain-or-ec2-public-dns
    DocumentRoot /var/www/restaurant-project/public

    <Directory /var/www/restaurant-project/public>
        AllowOverride All
        Require all granted
    </Directory>

    Alias /admin /var/www/restaurant-project/admin
    <Directory /var/www/restaurant-project/admin>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo systemctl restart httpd
sudo chown -R apache:apache /var/www/restaurant-project
```

Open `http://<EC2-public-DNS>/` for the site and `http://<EC2-public-DNS>/admin/login.php` for the dashboard.

## 7. Security checklist before going live

- [ ] Change the default admin password
- [ ] Put `.env` outside git (already in `.gitignore`) and set real production DB/S3 values
- [ ] Restrict RDS security group to EC2 only
- [ ] Use an IAM role instead of access keys on EC2
- [ ] Serve over HTTPS (ACM cert + ALB or CloudFront, or Let's Encrypt with certbot)
- [ ] Set `'secure' => true` in `includes/auth.php` session cookie params once HTTPS is live
- [ ] Consider putting `includes/` outside the web root entirely (one level above `public/`) — it already is in this structure, just don't create an Apache alias for it

## 8. Cost-saving tips (student/free-tier friendly)

- t3.micro EC2 + db.t3.micro RDS are both free-tier eligible for 12 months.
- S3 storage for a small image gallery costs pennies/month; enable S3 lifecycle rules if you expect to swap out lots of images.
- Stop the RDS/EC2 instances when not actively demoing to avoid idle charges.
