#!/usr/bin/python
import socket,time,os
VIDEO_DIR = "/tmp/video"

def get_serv_files():
	list = os.listdir(VIDEO_DIR)
	out="files:{"
	for file in list:
		out += "'"+str(file)+"':'"+time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(os.path.getmtime(VIDEO_DIR+"/"+file)))+"',"
	out += "}"
	return out

def get_command(c):
	if(c=='rec'):
		return '/home/pi/rec'
	if(c=='photo'):
		return 'raspistill -t 1 -n -o /tmp/cam/img.jpg'
	if(c=='stop'):
		return 'sudo killall raspivid h264_v4l2_rtspserver'
	if(c=='off'):
		return 'sudo shutdown -h now'
	if(c=='null'):
		return 'start!'

s = socket.socket()

s.bind(('',2346))
s.listen(2)

out = "NULL\n"
command_to_cl = "start!"

while(True):
	cls,cladr=s.accept()
	if(cladr[0]=='127.0.0.1'):#PHP
		print "PHP!"
		cls.send(out)
		command_to_cl = get_command(cls.recv(10))
		print command_to_cl
		print "ePHP!"

	
	else:#RPi
		out ="{date:'"+time.strftime('%Y-%m-%d %H:%M:%S')+"',files:{"
		out1 = cls.recv(2048)#get file list
		clfiles = eval(out1[6:])#file list to dict !DANGER
		servfiles = eval(get_serv_files()[6:])
		tocldata = "{command:'"+command_to_cl+"',diff_files:{"#data to client

		for clfn,clfd in clfiles.items():
			if clfd == servfiles.get(clfn):
				out += "'"+clfn+"':'"+clfd+"',"
			else:
				if(clfd=='recording'):
					out += "'"+clfn+"':'!recording',"
				else:
					out += "'"+clfn+"':'"+clfd+"!"+str(servfiles.get(clfn))+"',"
					tocldata += "'"+clfn+"',"
		tocldata += "}}"
		cls.send(tocldata)
		command_to_cl = "start!"

		out += "}}\n"
		
		print out+tocldata
		f = open('data.dat','w')
		f.write(out)
		f.close()

	cls.close()
"""
TODO:
commands from PHP (sockets)
rec, foto, stop rec, stop recive
"""