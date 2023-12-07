# Set crossfade default values to 0.0
# IMPORTANT: Disable native Crossfades in Azuracast!!!
# Workaround for issue: https://github.com/AzuraCast/AzuraCast/issues/6756
# Fix not yet available in stable release channel.
def my_crossfade(old, new) =
  cross.simple(old.source, new.source, fade_in=0.0, fade_out=0.0)
end
radio = cross(minimum=0.0, duration=0.0, my_crossfade, radio)