file="random"
cat ${file} | while read line; do
    echo "${line} $RANDOM"
done > tmp.txt
mv tmp.txt ${file}
