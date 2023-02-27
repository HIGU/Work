# Pythonの学習

<div style="text-align: right;">
作成日：2022/05/13
</div>


Pythonを学習する上での学習手順や注意点についてまとめる。

---

### 1. **環境構築**

Pythonプログラムを動かすために以下の手順で環境構築を行う。

前提として使用するPCは**Windows10 64bit版**とする。

Visual Studio Codeを用いる場合とVisual Studioを用いる場合の2パターンを紹介する。

<br>

#### ①.Visual Studio Codeを用いる場合

   1. **Visual Studio Codeをインストールする。**
      - 以下のファイルに導入手順が書かれているので手順にしたがってインストールと初期設定等を行う。
         ```
            \\192.168.4.92\private\●技術部1課共用\資料\VsCode関連\VSCode導入手順書.html
         ```
<br>

   1. **Pythonをインストールする。**
      - pc4-soft内にある以下のインストーラからインストールする。
         ```
            \\pc4-soft\アプリ\ソフトウェア事業部\104_Python\Python 3.9.5\python-3.9.5-amd64.exe
         ```
      - インストール手順と正しくインストールできたかの確認は[こちらのサイト](https://qiita.com/New_enpitsu_15/items/ee95bde0858e9f77acf0)を参考にする。
</br>

   3. **[参考記事](https://usimaru.net/vscode-python-helloworld/)内の手順にしたがってVisual Studio Code上でコードを実行する。**
      - 記事内の「**VSCodeでPythonを使えるようにしてみよう**」と「**VSCodeでPythonを動かしてみよう！**」内の手順にしたがって進めることでPythonのコードを実行できる環境を作る。
      - デバッグ実行方法については[こちらのサイト](https://atmarkit.itmedia.co.jp/ait/articles/2107/16/news029.html)が参考となる。

</br>

   4. **パッケージのインストール方法について理解しておく。**
      - Visual Studio Codeでのコンソール上で「`pip install`」コマンドを使ってパッケージをインストールする。
      - Visual Studio Codeのコンソール上で以下のコマンドを入力することでインストールができる。
         > **python -m pip install --user (ライブラリ名)**
      
         以下の2つのコマンドから「numpy」と「matplotlib」パッケージをインストールしてみる。
         > **python -m pip install --user numpy**
         > **python -m pip install --user matplotlib**
      - インストールしたパッケージを使用して[こちらのサイト](https://qiita.com/RinGoku/items/9a0e28ddac4663cd5b03)を参考にグラフを書いてみる。
      

<br>

#### ②.Visual Studioを用いる場合

   1. **Visual Studio 2019をインストールする。**
      - すでにインストールされている方はスキップしてよい
      - インストールされていなければ、pc4-soft内にある以下のインストーラからインストールする
         ```
            \\pc4-soft\アプリ\ソフトウェア事業部\vs_Community\vs_2019_Community\vs_Community.exe
         ```
<br>

   1. **Pythonをインストールする。**
      - pc4-soft内にある以下のインストーラからインストールする。
         ```
            \\pc4-soft\アプリ\ソフトウェア事業部\104_Python\Python 3.9.5\python-3.9.5-amd64.exe
         ```
      - インストール手順と正しくインストールできたかの確認は[こちらのサイト](https://qiita.com/New_enpitsu_15/items/ee95bde0858e9f77acf0)を参考にする。
<br>

   3. **[Microsoft公式サイト内のチュートリアル](https://docs.microsoft.com/ja-jp/visualstudio/python/tutorial-working-with-python-in-visual-studio-step-00-installation?view=vs-2019)内の手順にしたがってVisual Studio上でコードを実行する。**
      - チュートリアル内の**0番から3番までの手順**にしたがって進めることでPythonのコードを実行できる環境を作る。
      - **手順2**でコードを実装する際に、実行するPythonのバージョンが2.でインストールしたPythonのバージョンと異なる場合は、以下の手順で実行環境を変える必要がある。
        1. ソリューションエクスプローラー上の [**Python環境**] を右クリックして [**環境を追加**] を選択する
        2. 別画面が開くので画面右側の [**既存の環境**] を選択し、 [**環境(E)**] のドロップダウンメニューが [**Python 3.9(64bit)**] となっていることを確認して [**追加**] ボタンを押下する
        3. ソリューションエクスプローラー上の [**Python環境**] 下に [**Python 3.9(64bit)**] が追加されるので右クリックして [**環境のアクティブ化**] を選択する
      - **手順4**はデバッガーでの実行方法が記載されている。
<br>

   4. **パッケージのインストール方法について理解しておく。**
      - **上記チュートリアルでの手順5**にてパッケージのインストール方法が記載されている。
      - 「`pip install`」コマンドの代わりにウィンドウ上でパッケージをインストールすることができる。

<br>
<br>

---

### 2. **基礎文法を学ぶ**

基礎文法、標準ライブライリ、組み込み関数について以下の順番で学んでもらう。

<br>

#### 1. Python学習サイトの入門編を使って学習する。

   - [PythonエンジニアによるPython3学習サイトの入門編](https://www.python.ambitious-engineer.com/introduction-index)にある13項目について学ぶ。
   - 同サイトにて「Pythonとは？」や「Pythonのメリット・デメリット」についても記載されており、そこから知りたい方は[こちら](https://www.python.ambitious-engineer.com/archives/3034)から。
   - **ページ内には演習問題等は用意されていないため、自身でサンプルコードをコーディングして動作を確認しながら学習してほしい。**

<br>

#### 2. 練習問題を実際に解く。

以下の3つのサイトが用意する練習問題を実際にプログラミングして基本文法や標準ライブラリについて理解を深めてもらう。

   - (1) [Python3練習問題18問](http://yay.cla.kobe-u.ac.jp/~jm/edu/2016/PB/python3/python3ex01.html)
     - 解答の一例については各問題の右下に表示された「...」ボタンを押下すると表示される。


<br>

   - (2) [練習問題55本ノック](https://gotutiyan.hatenablog.com/entry/2020/04/14/174007)での練習の問題
     - 解答の一例はサイト内でも紹介されているが、[こちら](https://gotutiyan.hatenablog.com/entry/2020/04/14/174030)からも見ることができる。

<br>

   - (3) [東京大学情報教育センターPython学習ページ](https://utokyo-ipp.github.io/index.html)での練習問題
     - 「**1-1.数値演算**」から「**6-3.クラス**」までにある練習問題を解いてもらう。
      (7章以降は機械学習などの応用的な内容が含まれてくるためここでは演習対象から外す)
     - 解答の一例については章末に記載がされている。
     - **1.で学んだ学習サイトと比べると文法内容について網羅できていない部分もあるが、こちらも基礎文法についての説明があるので復習の意味も含めて説明を読みながら進めてほしい。**
 
<br>


#### (補足)その他に基礎文法や標準ライブライリについて参考になるもの

   1. **[python公式サイト](https://docs.python.org/ja/3.10/tutorial/index.html)**
      - 日本語での翻訳切替できるが直訳された部分の解釈は注意が必要となる。
      - Pythonのバージョンを選択できるので、バージョンによって文法やライブライリ内容が異なる場合には便利である。

<br>

   1. **社内共有のPython教育用資料**
      - 以下の技術部サーバ内にPython教育用で使用された資料がある。
         ```
            \\192.168.4.92\private\●技術部共用\新人教育\pythonの教材
         ```
      - 基本文法からPythonでの**データベース開発**や**Web開発**といった応用編までいくつかの資料があるのでこちらも活用できる。


<br>
<br>

---

### 3. **AIについて試す**

Pythonでできる事の事例として、AI(機械学習・深層学習)が挙げられる。

以下の技術部サーバ内にある外部研修での資料を読み進めることで試すことでができる。

(**※資料中のJupyter Notebookがなくとも問題ない**)

```
\\192.168.4.92\ideabox\研修記録\20220203-04_オープンソフトウェアライブラリを用いた人工知能(AI)活用技術\資料\オープンソフトウェアライブラリを用いた人工知能(AI)活用技術.pdf
```

<br>
<br>

---

### 4. **Python開発における注意点**

<br>

#### 1. **Pythonコーディング指針について**

   PythonはError・Warningを除去しなくても実行できる為、品質的に危険な側面がある。
   一定の品質を保つ目的で、開発時は以下の指針に従うこと。

   ```
   \\192.168.4.92\private\●技術部共用\部内ルール等\Pythonコーディング指針_1.0版_20200727.docx
   ```
<br>

#### 2. **PyInstallerでexe化について**
   Pythonについてexe化した場合、下記URLの様にトロイの木馬と誤認されるケースがある。
   [こちらのサイト](https://qiita.com/nobody_gonbe/items/5ffdd1a767c67256032e)を一読の上、作業時に記載のような手続きを取らないよう注意すること。

<br>

#### 3. **サイトからのダウンロードについて**

   Pythonで参考になるコード(impacket)があったので、[Python Package Index](https://pypi.org/)からダウンロードして解凍したところ、トロイの木馬型ウィルスを検出した。
   上記サイトは、Pythonの世界では標準的に使われているが、悪意のあるコードもアップされていることがあるようだ。

   PythonやJavascriptなど、インタプリタ型の言語のソースはコンパイラなしで動くので、ソースだけでも危険な場合がある。
   サンプルコードを参考にする際などでプレビュー等で済むものは済ます、どうしてもダウンロードしたい場合は、出どころや問題情報がないかチェックの上で使うようにし、ウィルス感染リスクを減らすよう徹底すること。




---

<div style="text-align: right;">
以上
</div>

<style>
h1 {
background: #dfefff;
box-shadow: 0px 0px 0px 5px #dfefff;
border: dashed 2px white;
padding: 0.2em 0.5em;
}

h2 {
position: relative;
padding: 0.6em;
background: #e0edff;
}

h2:after {
position: absolute;
content: '';
top: 100%;
left: 30px;
border: 15px solid transparent;
border-top: 15px solid #e0edff;
width: 0;
height: 0;
}
h3 {
background: linear-gradient(transparent 70%, #a7d6ff 70%);
}
h4 {
border-bottom: solid 3px #cce4ff;
position: relative;
}

h4:after {
position: absolute;
content: " ";
display: block;
border-bottom: solid 3px #5472cd;
bottom: -3px;
width: 20%;
}
h5 {
border-bottom: solid 3px black;
}
p {
font-size: 10pt;
}
li {
font-size: 10pt;
}

@media print {
.markdown-preview.markdown-preview {
    font-family:  'Yu Gothic', YuGothic, 'ヒラギノ角ゴ Pro', 'Hiragino Kaku Gothic Pro', 'メイリオ', 'Meiryo', sans-serif;
    font-size: 10pt;
}
}
img{display:block;transform:rotate(0deg);width:60%;height:auto;margin-left:20%;margin-right:auto;}

.page {
  page-break-after: always;
}
</style>