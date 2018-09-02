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

def create_callfile
	filename = '/tmp/dxalert.callfile'
	file = File.new(filename,'w')

	callfile = <<~CALLFILE
		Channel: SIP/voipms/4153140314
		CallerID: "(dx)ALERT" <8056667111>
		MaxRetries: 10
		RetryTime: 5
		Context: dxalert
		Extension: 1
	CALLFILE

	file.write(callfile)

	file.close

	FileUtils.mv(filename, '/var/spool/asterisk/outgoing')
end

db = Mysql2::Client.new(host: 'localhost', username: 'checker', password: 'niggerchecker', database: 'dxalert', reconnect: true)

at_exit do
	logger.info 'exiting'
	db.close
end

db.query('SELECT * FROM alerts', as: :hash, symbolize_keys: true).each do |alert|
	uri = URI(alert[:url])
	resp = Net::HTTP.get(uri)
	hash = Digest::SHA256.hexdigest(resp)
	update = db.prepare("UPDATE alerts SET hash=? WHERE url=?")
	if alert[:hash].nil?
		logger.info "storing first-time hash for alert url=#{uri} hash=#{hash}"
		update.execute(hash,alert[:url])
	else
		if hash == alert[:hash]
			logger.info "hash still same for alert url=#{uri} hash=#{hash}"
		else
			logger.info "hash changed for alert url=#{uri} hash.new=#{hash} hash.old=#{alert[:hash]}"
			update.execute(hash,alert[:url])
			create_callfile
		end
	end
end
