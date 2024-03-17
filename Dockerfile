FROM ubuntu:latest
# Run java app and php app
RUN apt-get update && apt-get install -y openjdk-21-jdk
# Copy the jar file
COPY target/PlanningPoker-1.0-SNAPSHOT.jar /usr/src/PlanningPoker-1.0-SNAPSHOT.jar
# Run the jar file
CMD ["java", "-jar", "/usr/src/PlanningPoker-1.0-SNAPSHOT.jar"]


