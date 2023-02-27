import tkinter
from tkinter import messagebox

#入力文字数制限用の関数
def limit_char(str):
    return len(str) <= 2

#ボタンがクリックされたら実行
def button_click():
    r_value = r_box.get()
    g_value = g_box.get()
    b_value = b_box.get()
    try:
        r_int = int(r_value, 16)
        g_int = int(g_value, 16)
        b_int = int(b_value, 16)
        r_value = '{:02x}'.format(r_int)
        g_value = '{:02x}'.format(g_int)
        b_value = '{:02x}'.format(b_int)
    except ValueError:
        messagebox.showerror("入力値エラー", "00~FFで入力してください。")
        return
    if (r_int < 0 or r_int >= 256):
        messagebox.showerror("入力値エラー", "Rを00~FFで入力してください。")
        return
    if (g_int < 0 or g_int >= 256):
        messagebox.showerror("入力値エラー", "Gを00~FFで入力してください。")
        return
    if (b_int < 0 or b_int >= 256):
        messagebox.showerror("入力値エラー", "Bを00~FFで入力してください。")
        return
    color_label['bg'] = '#'+r_value+g_value+b_value
    color_value_label['text'] = r_value+g_value+b_value

#ウインドウの作成
root = tkinter.Tk()
root.title("Python GUI")
root.geometry("360x240")

vc = root.register(limit_char)

#入力欄の作成
r_box = tkinter.Entry(width=50, validate='key', validatecommand=(vc, '%P'))
r_box.place(x=10, y=100, width=50)
g_box = tkinter.Entry(width=50, validate='key', validatecommand=(vc, '%P'))
g_box.place(x=70, y=100, width=50)
b_box = tkinter.Entry(width=50, validate='key', validatecommand=(vc, '%P'))
b_box.place(x=130, y=100, width=50)

#ラベルの作成
r_label = tkinter.Label(text="R(00~FF)")
r_label.place(x=10, y=70)
g_label = tkinter.Label(text="G(00~FF)")
g_label.place(x=70, y=70)
b_label = tkinter.Label(text="B(00~FF)")
b_label.place(x=130, y=70)

#色表示用
color_label = tkinter.Label()
color_label.place(x=90, y=20, width=180, height=50)
color_label['bg'] = '#FFFFFF'
color_value_label = tkinter.Label(text='FFFFFF')
color_value_label.place(x=150, y=0, width=60)

#ボタンの作成
button = tkinter.Button(text="実行ボタン",command=button_click)
button.place(x=10, y=130)

#ウインドウの描画
root.mainloop()