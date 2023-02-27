import pydicom
import matplotlib.pyplot as plt
import glob
import numpy as np
import tkinter
from tkinter import filedialog
import pickle
import os
import sys

#Dicomのパス
#C:\\Users\\PC1901-24\\Desktop\\Vitrea_memo\\LungStressMapping\\Slicer\\DICOM\\ScalarVolume_18

#指定したディレクトリ下にあるdcm画像を読み込んで表示

def remove_keymap_conflicts(new_keys_set):
    for prop in plt.rcParams:
        if prop.startswith('keymap.'):
            keys = plt.rcParams[prop]
            remove_list = set(keys) & new_keys_set
            for key in remove_list:
                keys.remove(key)

def multi_slice_viewer(volume):
    remove_keymap_conflicts({'down', 'up'})
    fig, ax = plt.subplots()
    ax.volume = volume
    ax.index = int(volume.shape[0] / 2)
    ax.imshow(volume[ax.index-1])
    ax.set_title(ax.index)
    fig.canvas.mpl_connect('key_press_event', process_key)

def process_key(event):
    fig = event.canvas.figure
    ax = fig.axes[0]
    if event.key == 'down':
        previous_slice(ax)
    elif event.key == 'up':
        next_slice(ax)
    fig.canvas.draw()
    ax.set_title(ax.index)

def previous_slice(ax):
    volume = ax.volume
    ax.index = (ax.index - 1) % volume.shape[0]
    ax.images[0].set_array(volume[ax.index])

def next_slice(ax):
    volume = ax.volume
    ax.index = (ax.index + 1) % volume.shape[0]
    ax.images[0].set_array(volume[ax.index])


pickle_file_name = 'DicomPath.pkl'
if not(os.path.exists(pickle_file_name)):
    disc = {'path': os.getcwd()}
    save_file = open(pickle_file_name, mode='wb')
    pickle.dump(disc, save_file)
    save_file.close

#pklの保存直後に開くとエラーになる
#ダイアログの初期位置を読み込む
with open(pickle_file_name, mode='rb') as f:
    try:
        pickled = pickle.load(f)
    except FileNotFoundError:
        #TODO: エラー検知できない
        pickled = {'path': 'C:\\'}

initialdir = pickled['path']
print(initialdir)
folder_path = tkinter.filedialog.askdirectory(initialdir = initialdir)
if folder_path == '':
    #キャンセルが選択されたら終了
    print('Selected Cancel')
    sys.exit(0)

#選択ディレクトリの位置を保存
dict1 = {'path': folder_path}
save_file = open(pickle_file_name, mode='wb')
pickle.dump(dict1, save_file)
save_file.close
print(folder_path)

files = glob.glob(folder_path + '\\*.dcm')
if len(files) == 0:
    print('Not Found dcm file')
    sys.exit(0)

print("Start to Read Image")
#画像サイズは512*512固定
imgs = np.empty((0, 512, 512))
is_first = True
for file in files:
    py_file = pydicom.dcmread(file)
    if is_first:
        #タグ情報の出力
        print(py_file)
        is_first = False
    imgs = np.vstack((imgs, [py_file.pixel_array]))
multi_slice_viewer(imgs)
plt.show()
