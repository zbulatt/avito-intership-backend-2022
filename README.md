# Микросервис для работы с балансом пользователей

*API не удовлетворяет требованию по языку, однако я буду благодарен за ревью и рад любым замечаниям и рекомендациям!*

## Маршруты

### Метод начисления средств на баланс

`POST /refill`

*Принимает id пользователя **user_id** и количество **amount** средств для начисления*
```
{
  "user_id": 1,
  "amount": 1000
}
```

### Метод резервирования средств с основного баланса на отдельном счете

`POST /reserve`

*Принимает id пользователя **user_id**, id услуги **service_id**, id заказа **order_id**, и стоимость **amount***
```
{
  "user_id": 1,
  "service_id": 10,
  "order_id": 3,
  "amount": 1000
}
```

### Метод признания выручки

Списывает из резерва деньги, добавляет данные в отчет для бухгалтерии

`POST /transfer`

*Принимает id пользователя **user_id**, id услуги **service_id**, id заказа **order_id**, и стоимость **amount***
```
{
  "user_id": 1,
  "service_id": 10,
  "order_id": 3,
  "amount": 1000
}
```

### Метод получения баланса пользователя

`POST /balance`

*Принимает id пользователя **user_id***
```
{
  "user_id": 1
}
```

### Метод получения месячного отчета

`POST /report`

*Принимает год **year*** и месяц **month**
```
{
  "year": 2022,
  "month": 11
}
```

### Метод получения списка транзакций

`POST /history`

*Принимает id пользователя **user_id***
```
{
  "user_id": 1
}
```