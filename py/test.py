from escpos.printer import Serial
import requests
import textwrap
import pendulum

LINE_WIDTH = 32
URL_BASE = "http://localhost/advweb-uas-v2/api"

thermal = Serial(
    devfile="COM3",
    baudrate=9600,
    bytesize=8,
    parity="N",
    stopbits=1,
    timeout=1,
    dsrdtr=True,
)

thermal.set_with_default(align="left", font="a", bold=False, underline=0, density=9)


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


def fetch_order(order_id: int) -> dict:
    """sample
    {
        "id": 2,
        "order_date": "2025-12-13T13:37:01.000000Z",
        "subtotal": 49000,
        "tax_amount": 4900,
        "total_amount": 53900,
        "payment_method": "QRIS",
        "payment_status": "PAID",
        "created_at": "2025-12-13T13:37:01.000000Z",
        "updated_at": "2025-12-13T13:37:01.000000Z",
        "order_items": [
            {
                "id": 3,
                "order_id": 2,
                "menu_item_id": 7,
                "menu_name": "Sate Ayam (10x)",
                "quantity": 1,
                "price_at_time": 35000,
                "subtotal": 35000,
                "created_at": "2025-12-13T13:37:01.000000Z",
                "updated_at": "2025-12-13T13:37:01.000000Z"
            },
            {
                "id": 4,
                "order_id": 2,
                "menu_item_id": 11,
                "menu_name": "Mineral Water 600ml",
                "quantity": 1,
                "price_at_time": 4000,
                "subtotal": 4000,
                "created_at": "2025-12-13T13:37:01.000000Z",
                "updated_at": "2025-12-13T13:37:01.000000Z"
            },
            {
                "id": 5,
                "order_id": 2,
                "menu_item_id": 15,
                "menu_name": "Telur Dadar",
                "quantity": 2,
                "price_at_time": 5000,
                "subtotal": 10000,
                "created_at": "2025-12-13T13:37:01.000000Z",
                "updated_at": "2025-12-13T13:37:01.000000Z"
            }
        ]
    }
    """

    r = requests.get(f"{URL_BASE}/orders/{order_id}", timeout=10)
    r.raise_for_status()
    return r.json()


def fmt_int(n, sep=".") -> str:
    s = f"{int(n):,}"
    return s.replace(",", sep)


def print_receipt(order: dict):
    set_big("center")
    thermal.textln("Resto POS")

    set_normal("center")
    thermal.textln("Jl. Raya Kedung Baruk No.98, Surabaya")
    thermal.textln("Telp: (031) 8721731")
    hr()

    set_normal("left")
    thermal.textln("No    : " + str(order["id"]))
    thermal.textln("Kasir : Admin")
    # "order_date": "2025-12-13T13:37:01.000000Z",
    thermal.textln(
        "Tgl   : "
        + pendulum.parse(order["order_date"])
        .in_timezone("Asia/Jakarta")
        .strftime("%d-%m-%Y %H:%M:%S")
    )
    hr()

    for item in order.get("order_items", []):
        text_wrap(item.get("menu_name", ""), LINE_WIDTH)
        thermal.textln(
            left_right(
                f'{fmt_int(item.get("price_at_time", 0), sep=".")} x {fmt_int(item.get("quantity", 0), sep=".")}',
                fmt_int(item.get("subtotal", 0), sep="."),
            )
        )

    hr()
    thermal.textln("Total : " + fmt_int(order.get("total_amount", 0), sep="."))
    thermal.textln(
        left_right(
            str(order.get("payment_method", "QRIS")),
            fmt_int(order.get("total_amount", 0), sep="."),
        )
    )
    hr()

    set_normal("center")
    thermal.textln("Terima kasih")
    thermal.textln("Silahkan datang kembali")
    thermal.ln(3)


if __name__ == "__main__":
    try:
        order = fetch_order(order_id=2)
        print_receipt(order)
    finally:
        thermal.close()
