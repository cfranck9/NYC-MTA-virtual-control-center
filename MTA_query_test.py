from google.transit import gtfs_realtime_pb2
import requests

feed = gtfs_realtime_pb2.FeedMessage()
response = requests.get('http://datamine.mta.info/mta_esi.php?key=<your key>&feed_id=51')
feed.ParseFromString(response.content)

count = 0
this_route_id = ''
this_stop_time_update = {}

for entity in feed.entity:
    if entity.HasField('trip_update'):
        # print(entity)
        this_trip_id = entity.trip_update.trip.trip_id
        this_route_id = entity.trip_update.trip.route_id
        this_stop_time_update = entity.trip_update.stop_time_update
        # print(this_trip_id, 'route =', this_route_id)
    if entity.HasField('vehicle'):
        # print(entity)
        this_trip_id = entity.vehicle.trip.trip_id
        this_current_stop_sequence = entity.vehicle.current_stop_sequence
        # print('trip id =', this_trip_id, 'route =', this_route_id, 'last stop # :', this_current_stop_sequence)
        # for stop_time in this_stop_time_update:
        #         print(stop_time.stop_id, time.ctime(stop_time.arrival.time))
    # count += 1
    # if count>30:
    #     break