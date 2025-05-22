bin/console doctrine:schema:drop --force
sleep 1
bin/console doctrine:schema:create
sleep 1
for i in {1..5}; do
  echo "Running batch $i..."
  bin/console app:game:bots-generate --amount 200
  sleep 1
done