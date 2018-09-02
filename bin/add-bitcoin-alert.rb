#!/usr/bin/ruby

require 'mysql2'
require 'logger'
require 'net/http'
require 'digest'
require 'fileutils'

PROGRAM = File.basename($0,'.rb')

logger = Logger.new(STDOUT)
logger.progname = PROGRAM
logger.formatter = proc { |s,d,p,m| "[#{d}] #{s} #{p}: #{m}\n" }

db = Mysql2::Client.new(host: 'localhost', username: 'checker', password: 'niggerchecker', database: 'dxalert', reconnect: true)

at_exit do
	logger.info 'exiting'
	db.close
end

address=ARGV.first

url="https://blockchain.info/q/getreceivedbyaddress/#{address}?confirmations=1"

update = db.prepare('INSERT INTO alerts (url) VALUES (?)')

update.execute(url)

logger.info "adding alert for url #{url}"
