
test_list=[0, 1, 2, 3, 4]
f = open('test_file1.txt', 'w', encoding='UTF-8')
for a in test_list:
    f.write('%d\n' % a)