
# determine which tests to run
while getopts t: flag
do
    case "${flag}" in
        t) type=${OPTARG};;
    esac
done

# only run the lint tests
run_lint_tests () {
	./php-lint.sh ".."
}

# only run the unit tests
run_unit_tests () {
	printf "\n--- running unit tests...\n"
	for i in `ls *Test.php`; do
		phpunit $i $1 2>&1 > $1.out;
		if [[ $? -eq 0 ]]; then
			echo "PASS $i"
			rm -f $1.out
		else
			echo "FAIL $i: $?"
		fi
	done

	tests_run=`grep 'function test' *Test.php | wc -l`
	printf "\n--- Number of tests run: $tests_run\n"
}

# run all the tests
run_all_tests () {
    echo "--- Run all tests"
	run_lint_tests
	run_unit_tests
}


tests='ALL'
if [ -z ${type+x} ]; then
	run_all_tests; else

	case $type in
		'ALL')
			run_all_tests
			;;
		'L')
			run_lint_tests
			;;
		'U')
			run_unit_tests
			;;
		*)
			echo "error, choose ALL, U or L";
			exit;
		;;
	esac
fi

echo ""


