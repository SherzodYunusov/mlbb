CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "telegram_id" integer not null,
  "username" varchar,
  "first_name" varchar not null,
  "role" varchar check("role" in('user', 'admin', 'garant')) not null default 'user',
  "balance" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_telegram_id_unique" on "users"("telegram_id");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "accounts"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "price" numeric not null,
  "heroes_count" integer not null,
  "skins_count" integer not null,
  "collection_level" varchar not null,
  "description" text,
  "ready_for_transfer" tinyint(1) not null default '0',
  "status" varchar check("status" in('draft', 'pending', 'active', 'rejected', 'sold')) not null default 'draft',
  "created_at" datetime,
  "updated_at" datetime,
  "channel_message_id" integer,
  "video_size" numeric,
  "last_confirmed_at" datetime,
  "checkin_sent_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "account_media"(
  "id" integer primary key autoincrement not null,
  "account_id" integer not null,
  "file_id" varchar not null,
  "type" varchar check("type" in('image', 'video')) not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("account_id") references "accounts"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "deals"(
  "id" integer primary key autoincrement not null,
  "account_id" integer not null,
  "buyer_id" integer not null,
  "seller_id" integer not null,
  "status" varchar check("status" in('pending_admin', 'ongoing', 'completed', 'cancelled')) not null default 'pending_admin',
  "admin_message_id" integer,
  "group_chat_id" integer,
  "topic_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("account_id") references "accounts"("id") on delete cascade,
  foreign key("buyer_id") references "users"("id"),
  foreign key("seller_id") references "users"("id")
);
CREATE TABLE IF NOT EXISTS "login_tokens"(
  "id" integer primary key autoincrement not null,
  "token" varchar not null,
  "user_id" integer not null,
  "expires_at" datetime not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "login_tokens_token_unique" on "login_tokens"("token");
CREATE TABLE IF NOT EXISTS "comments"(
  "id" integer primary key autoincrement not null,
  "account_id" integer not null,
  "sender_id" integer not null,
  "message" text not null,
  "created_at" datetime,
  "updated_at" datetime,
  "parent_id" integer,
  "is_hidden" tinyint(1) not null default '0',
  "edited_at" datetime,
  foreign key("sender_id") references users("id") on delete cascade on update no action,
  foreign key("account_id") references accounts("id") on delete cascade on update no action,
  foreign key("parent_id") references "comments"("id") on delete set null
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_04_17_000001_create_accounts_table',1);
INSERT INTO migrations VALUES(5,'2026_04_17_000002_create_account_media_table',1);
INSERT INTO migrations VALUES(6,'2026_04_17_000003_create_comments_table',1);
INSERT INTO migrations VALUES(7,'2026_04_17_000004_add_channel_message_id_to_accounts',2);
INSERT INTO migrations VALUES(8,'2026_04_17_000005_create_deals_table',3);
INSERT INTO migrations VALUES(9,'2026_04_19_000001_create_login_tokens_table',4);
INSERT INTO migrations VALUES(10,'2026_04_19_000002_add_video_size_to_accounts',5);
INSERT INTO migrations VALUES(11,'2026_04_20_000001_upgrade_comments_table',6);
INSERT INTO migrations VALUES(12,'2026_04_20_000002_update_collection_levels_in_accounts',7);
INSERT INTO migrations VALUES(13,'2026_04_21_000001_add_checkin_fields_to_accounts',8);
