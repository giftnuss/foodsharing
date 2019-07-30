Functional tests currently (as of may, 2019) don't work properly because the foodsharing application relies on superglobals that are not available inside.

Until this is fixed, there is for example no session support in functional tests. That is why there currently is only the LoginApi test.

Please be aware of this when writing more functional tests and maybe use API, htmlacceptance or unit instead :-)
