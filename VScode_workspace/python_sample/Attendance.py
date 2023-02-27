import matplotlib.pyplot as plt
import pandas as pd


if __name__ == '__main__':
    data = {
        'Tokyo': [27, 23, 27, 24, 25, 23, 26],
        'Osaka': [26, 23, 27, 28, 24, 22, 27],
    }

    df = pd.DataFrame(data)

    fig, ax = plt.subplots(figsize=(3, 3))

    ax.axis('off')
    ax.axis('tight')

    tb = ax.table(cellText=df.values,
                  colLabels=df.columns,
                  bbox=[0, 0, 1, 1],
                  )

    tb[0, 0].set_facecolor('#363636')
    tb[0, 1].set_facecolor('#363636')
    tb[0, 0].set_text_props(color='w')
    tb[0, 1].set_text_props(color='w')

    plt.show()