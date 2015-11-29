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


s = socket.socket()

s.bind(('',2346))
s.listen(1)

command_to_cl = "start!"

while(True):
	cls,cladr=s.accept()

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