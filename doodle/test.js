const cart = [{ id: 1, name: "xdd", qty: 1, price: 1000, item_discount: 0.2 }];

const x = cart.map((i) => ({
    id: i.id,
    menu_name: i.name, // Manual name override
    quantity: i.qty,
    price: parseInt(i.price), // Manual price
    subtotal: Math.round(i.price * i.qty - (i.item_discount || 0)), // Manual subtotal per item
    item_discount: parseFloat(i.item_discount || 0),
}));

console.log(x);

const y = [];
cart.forEach((i) => {
    y.push({
        id: i.id,
        menu_name: i.name, // Manual name override
        quantity: i.qty,
        price: parseInt(i.price), // Manual price
        subtotal: Math.round(i.price * i.qty - (i.item_discount || 0)), // Manual subtotal per item
        item_discount: parseFloat(i.item_discount || 0),
    });
});

console.log(y);
