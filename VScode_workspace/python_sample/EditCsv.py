import tkinter as tk
import tkinter.ttk as ttk
import pandas as pd

# https://qiita.com/R1nY1x1/items/26c056d2ef0a3848215c

def selected(event):
    for item in tree.selection():
        print(item, tree.item(item))


def main():
    # Load .csv
    df = pd.read_csv("tasks.csv", encoding='shift-jis')
    # create Window and Treeview
    root = tk.Tk()
    tree = ttk.Treeview(root, show='headings')
    # set Treeview columns
    tree['column'] = ("No",) + tuple(df)
    # set header
    tree.heading("No", text="No")
    for c in df:
        tree.heading(c, text=c)
    # set cells on the row
    for i, row in enumerate(df.itertuples()):
        tree.insert("", "end", tags=i, values=row)
    # set layout
    tree.pack()
    # bind action
    tree.bind('<<TreeviewSelect>>', selected)
    # set loop
    root.mainloop()


if __name__ == '__main__':
    main()