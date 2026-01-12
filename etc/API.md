# API Documentation

This document describes the API endpoints provided by the Laravel backend.

## Base URL

All API routes are prefixed with `/api`.

## Authentication

Some endpoints may require authentication using Laravel Sanctum (e.g., `/user`).  
Authenticated requests should include an `Authorization: Bearer <token>` header.

---

## 1. Menu Items

Manage the food and drink items available on the menu.

### List All Menu Items

-   **URL**: `/menu-items`
-   **Method**: `GET`
-   **Description**: distinct list of all available menu items.
-   **Response**: Array of menu item objects.
    ```json
    [
        {
            "id": 1,
            "name": "Nasi Goreng",
            "price": 15000,
            "image_asset": "nasi_goreng.jpg",
            "created_at": "...",
            "updated_at": "..."
        }
    ]
    ```

### Get Menu Item

-   **URL**: `/menu-items/{id}`
-   **Method**: `GET`
-   **Description**: Retrieve a single menu item by ID.
-   **Response**: Menu item object or 404 Not Found.

### Create Menu Item

-   **URL**: `/menu-items`
-   **Method**: `POST`
-   **Body Parameters**:
    -   `name` (required, string, max:255)
    -   `price` (required, integer, min:0)
    -   `image_asset` (optional, string) - filename or path relative to assets
-   **Response**: Created menu item object (HTTP 201).

### Update Menu Item

-   **URL**: `/menu-items/{id}`
-   **Method**: `PUT` or `PATCH`
-   **Body Parameters** (all optional):
    -   `name` (string)
    -   `price` (integer)
    -   `image_asset` (string)
-   **Response**: Updated menu item object.

### Delete Menu Item

-   **URL**: `/menu-items/{id}`
-   **Method**: `DELETE`
-   **Description**: Deletes the specified menu item.
-   **Response**: HTTP 204 No Content.

---

## 2. Orders

Manage customer orders.

### List All Orders

-   **URL**: `/orders`
-   **Method**: `GET`
-   **Description**: Returns all orders including their associated items.
-   **Response**: Array of orders with `orderItems`.

### Get Order

-   **URL**: `/orders/{id}`
-   **Method**: `GET`
-   **Description**: Retrieve a single order with its items.
-   **Response**: Order object with `orderItems` nested.

### Create Order

-   **URL**: `/orders`
-   **Method**: `POST`
-   **Description**: Creates a new order. Calculates subtotal, tax (10%), and total automatically.
-   **Body Parameters**:
    -   `payment_method` (required, string)
    -   `table_number` (required, string)
    -   `items` (required, array of objects):
        -   `id` (required, integer) - Menu item ID
        -   `quantity` (required, integer, min:1)
-   **Example Request**:
    ```json
    {
        "payment_method": "cash",
        "table_number": "12",
        "items": [
            { "id": 1, "quantity": 2 },
            { "id": 5, "quantity": 1 }
        ]
    }
    ```
-   **Response**: Created Order object with detailed `orderItems`. Status defaults to `PAID`.

### Update Order Status

-   **URL**: `/orders/{id}`
-   **Method**: `PUT`
-   **Description**: Currently only allows updating the `payment_status`.
-   **Body Parameters**:
    -   `payment_status` (string)
-   **Response**: Updated Order object.

### Delete Order

-   **URL**: `/orders/{id}`
-   **Method**: `DELETE`
-   **Description**: _Currently disabled in controller logic._

---

## 3. Printing

Endoints for thermal printer integration.

### Print Order

-   **URL**: `/orders/{id}/print`
-   **Method**: `POST`
-   **Description**: Sends the order details to a local Python Flask bridge running on `localhost:8800`.
-   **Response**:
    -   Success: `{"message": "Print job sent successfully"}`
    -   Error (500/503): Contains error message from the bridge or connection failure.
