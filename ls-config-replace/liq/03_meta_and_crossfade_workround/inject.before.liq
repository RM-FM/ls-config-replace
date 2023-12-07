def metadata_updated_2(m) =
	def f() =

		j = json()

		if (m["song_id"] != "") then
			j.add("song_id", m["song_id"])
			j.add("media_id", m["media_id"])
			j.add("playlist_id", m["playlist_id"])
		end
		
		if ((m["artist"] != "" or m["title"] != "") and m["song_id"] == "") then
			j.add("artist", m["artist"])
			j.add("title", m["title"])
		end

		_ = azuracast_api_call(
			"feedback",
			json.stringify(j)
		)	

	end

	if (m["artist"] != "" or m["title"] != "" or m["song_id"] != "") then
		thread.run(f)
	end

end
radio.on_metadata(metadata_updated_2)

last_metadata = ref([])
def handle_jingle_mode_2(m) =
	if (m["jingle_mode"] == "true") then
		last_metadata()
	else
		last_metadata.set(m)
		m
	end
end
radio = metadata.map(update=false, strip=true, handle_jingle_mode_2, radio)

def rm_crossfade(old, new) =
  cross.simple(old.source, new.source, fade_in=0., fade_out=0.)
end
radio = cross(minimum=0., duration=0., rm_crossfade, radio)