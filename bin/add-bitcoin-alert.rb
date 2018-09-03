#!/usr/bin/ruby

require 'mysql2'
require 'logger'
require 'net/http'
require 'digest'
require 'fileutils'

PROGRAM = File.basename($0,'.rb')

if ARGV.empty?
	print "\nusage: #{File.basename $0} (id bitcoin_address)...\n\n"
	exit
end

logger = Logger.new(STDOUT)
logger.progname = PROGRAM
logger.formatter = proc { |s,d,p,m| "[#{d}] #{s} #{p}: #{m}\n" }

db = Mysql2::Client.new(host: 'localhost', username: 'checker', password: 'niggerchecker', database: 'dxalert', reconnect: true)

at_exit do
	logger.info 'exiting'
	db.close
end

def mkurl(address)
	return "https://blockchain.info/q/getreceivedbyaddress/#{address}?confirmations=1"
end

ARGV.each_slice(2) do |id,address|
	url = mkurl address
	update = db.prepare('INSERT IGNORE INTO alerts (id,url) VALUES (?,?)')
	update.execute(id,url)
	logger.info "adding alert id=#{id} url=#{url}"
end
