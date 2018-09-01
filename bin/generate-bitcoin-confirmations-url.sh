#!/bin/bash

if [ -z "$1" ]; then
	echo
	echo usage: `basename $0` bitcoin_address
	echo
	exit
fi

echo "https://blockchain.info/q/getreceivedbyaddress/$1?confirmations=1"
