from escpos.printer import Serial
from flask import Flask, request, jsonify
import textwrap
import pendulum
import atexit

# -------------------
# Printer configuration
# -------------------
LINE_WIDTH = 32

thermal = Serial(
    devfile="COM3",
    baudrate=9600,
    bytesize=8,
    parity="N",
    stopbits=1,
    timeout=1,
    dsrdtr=True,
)

# default printer settings
thermal.set_with_default(
    align="left",
    font="a",
    bold=False,
    underline=0,
    density=9,
)


# Ensure printer is closed cleanly on exit
@atexit.register
def close_printer():
    try:
        thermal.close()
    except Exception:
        pass


# -------------------
# Helper functions
# -------------------
def set_normal(align: str = "left"):
    thermal.set(align=align, normal_textsize=True, bold=False, underline=0)


def set_big(align: str = "center"):
    thermal.set(
        align=align, double_width=True, double_height=True, bold=False, underline=0
    )


def left_right(line_left: str, line_right: str, width: int = LINE_WIDTH) -> str:
    line_left = (line_left or "")[:width]
    line_right = (line_right or "")[:width]
    space = width - len(line_left) - len(line_right)
    if space < 1:
        space = 1
    return f"{line_left}{' ' * space}{line_right}"


def text_wrap(s: str, width: int = LINE_WIDTH):
    for part in textwrap.wrap(
        s or "", width=width, break_long_words=True, break_on_hyphens=False
    ):
        thermal.textln(part)


def hr(char: str = "-"):
    set_normal("left")
    thermal.textln(char * LINE_WIDTH)


def fmt_int(n, sep=".") -> str:
    s = f"{int(n):,}"
    return s.replace(",", sep)


# -------------------
# Core print function (expects same JSON shape as fetch_order)
# -------------------
def print_receipt(order: dict):
    """
    Expects JSON like:
    {
    "id": 1,
    "order_date": "2026-01-12T10:43:31.000000Z",
    "subtotal": 79000,
    "tax_amount": 5925,
    "total_amount": 65175,
    "payment_method": "QRIS",
    "payment_status": "PAID",
    "table_number": "12",
    "tax_percent": "10.00",
    "global_discount_percent": "25.00",
    "created_at": "2026-01-12T10:43:31.000000Z",
    "updated_at": "2026-01-12T10:43:31.000000Z",
    "order_items": [
        {
        "id": 1,
        "order_id": 1,
        "menu_item_id": 8,
        "menu_name": "Aqua",
        "quantity": 4,
        "price_at_time": 6000,
        "subtotal": 23000,
        "item_discount": "1000.00",
        "created_at": "2026-01-12T10:43:31.000000Z",
        "updated_at": "2026-01-12T10:43:31.000000Z"
        },
        {
        "id": 2,
        "order_id": 1,
        "menu_item_id": 4,
        "menu_name": "Sate Ayam (10x)",
        "quantity": 1,
        "price_at_time": 35000,
        "subtotal": 35000,
        "item_discount": "0.00",
        "created_at": "2026-01-12T10:43:31.000000Z",
        "updated_at": "2026-01-12T10:43:31.000000Z"
        },
        {
        "id": 3,
        "order_id": 1,
        "menu_item_id": 12,
        "menu_name": "Telur Dadar",
        "quantity": 3,
        "price_at_time": 5000,
        "subtotal": 15000,
        "item_discount": "0.00",
        "created_at": "2026-01-12T10:43:31.000000Z",
        "updated_at": "2026-01-12T10:43:31.000000Z"
        },
        {
        "id": 4,
        "order_id": 1,
        "menu_item_id": 11,
        "menu_name": "Kerupuk Putih",
        "quantity": 3,
        "price_at_time": 2000,
        "subtotal": 6000,
        "item_discount": "0.00",
        "created_at": "2026-01-12T10:43:31.000000Z",
        "updated_at": "2026-01-12T10:43:31.000000Z"
        }
    ]
    }
    """
    # Header
    set_big("center")
    thermal.textln("Resto POS")

    set_normal("center")
    thermal.textln("Jl. Raya Kedung Baruk No.98,\nSurabaya. Telp: (031) 8721731")
    hr()

    # Order info
    set_normal("left")
    thermal.textln(left_right(f"OrderID: #{str(order.get('id', ''))}", ""))

    order_date_str = order.get("order_date")
    if order_date_str:
        dt_wib = pendulum.parse(order_date_str).in_timezone("Asia/Jakarta")
        thermal.textln("Date   : " + dt_wib.strftime("%d-%m-%Y %H:%M:%S"))
    else:
        thermal.textln("Date   : -")

    thermal.textln(f"Table  : {order.get('table_number', '-')}")
    thermal.textln(f"Pay    : {str(order.get('payment_method', 'QRIS'))}")

    hr()

    # Items
    order_items = order.get("order_items", [])
    for i, item in enumerate(order_items):
        text_wrap(item.get("menu_name", ""), LINE_WIDTH)
        price = float(item.get("price_at_time", 0))
        qty = int(item.get("quantity", 0))
        sub = float(item.get("subtotal", 0))

        left = f"{fmt_int(price, sep='.')} x {fmt_int(qty, sep='.')}"
        right = fmt_int(sub, sep=".")
        thermal.textln(left_right(left, right))

        # Item Discount
        item_disc = float(item.get("item_discount", 0))
        if item_disc > 0:
            thermal.textln(f"(Discount: -{fmt_int(item_disc, sep='.')})")

        if not i == len(order_items) - 1:
            thermal.ln(1)

    hr()

    # Totals
    # Parse values
    subtotal = float(order.get("subtotal", 0))
    total_amount = float(order.get("total_amount", 0))
    tax_amount = float(order.get("tax_amount", 0))

    # Global Discount
    global_disc_percent = float(order.get("global_discount_percent", 0))
    # Calculate global discount amount: Subtotal * (Percent / 100)
    global_disc_amount = subtotal * (global_disc_percent / 100)

    # Total Before Tax
    total_before_tax = subtotal - global_disc_amount

    # Tax Percent
    tax_percent = float(order.get("tax_percent", 0))

    # Print Totals
    thermal.textln(left_right("Subtotal", fmt_int(subtotal, sep=".")))

    if global_disc_amount > 0:
        thermal.textln(
            left_right(
                f"Global Disc ({int(global_disc_percent)}%)",
                f"-{fmt_int(global_disc_amount, sep='.')}",
            )
        )

    thermal.textln(left_right("Total Before Tax", fmt_int(total_before_tax, sep=".")))

    thermal.textln(
        left_right(f"Tax ({int(tax_percent)}%)", fmt_int(tax_amount, sep="."))
    )

    set_normal("left")  # Ensure bold/size reset
    hr()
    thermal.set(bold=True)
    thermal.textln(left_right("Total", fmt_int(total_amount, sep=".")))
    set_normal("left")

    hr()

    # Footer
    set_normal("center")
    thermal.textln("Terima kasih")
    thermal.textln("Silahkan datang kembali")
    thermal.ln(3)


# -------------------
# Flask app
# -------------------
app = Flask(__name__)


@app.post("/print/order")
def print_order():
    """
    Accepts JSON identical to fetch_order() response.
    Example POST to:
      http://localhost:8800/print/order
    """
    data = request.get_json(silent=True)

    if not isinstance(data, dict):
        return jsonify({"error": "Invalid JSON payload"}), 400

    try:
        print_receipt(data)
        return jsonify({"status": "ok"}), 200
    except Exception as exc:
        app.logger.exception("Printing failed")
        return jsonify({"error": str(exc)}), 500


if __name__ == "__main__":
    # Listen on port 8800 as requested
    app.run(host="0.0.0.0", port=8800)
