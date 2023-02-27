import pydicom

file = pydicom.dcmread("C:\\Users\\PC1901-24\\Desktop\\Vitrea_memo\\LungStressMapping\\Slicer\\DICOM\\SlicerDataBundle.dcm")
#メタデータの表示
print(file)