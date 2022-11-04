create table user_balance
(
    user_id int unsigned auto_increment primary key,
    balance int unsigned default 0 not null
);

create table reserve
(
    id         int unsigned auto_increment primary key,
    order_id   int unsigned not null,
    user_id    int unsigned not null,
    service_id int unsigned not null,
    amount     int unsigned not null
);

create table transactions
(
    user_id    int unsigned not null,
    service_id int unsigned not null,
    amount     int unsigned not null,
    date       datetime     not null
);

create table history
(
    user_id    int unsigned not null,
    service_id int unsigned not null,
    amount     int unsigned not null,
    date       datetime     not null
);
