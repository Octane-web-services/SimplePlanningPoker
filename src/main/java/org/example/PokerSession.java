package org.example;

import java.util.*;

public class PokerSession {


    public String sessionName;
    public int[] cardValues;
    ArrayList<String> Players = new ArrayList<>();

    HashMap<String, Integer> playerVotes = new HashMap<>();

    UUID sessionID;

    float lastVote = -1;
    public PokerSession(String sessionName, int[] cardValues, UUID sessionID) {
        this.sessionName = sessionName;
        this.cardValues = cardValues;
        this.sessionID = sessionID;
    }

    public String getSessionDetails() {
        if (lastVote == -1) {
            return String.format("{\"Session\": \"%s\", \"Cards\": %s, \"Last Vote\": \"Not Finished\", \"SessionID\": \"%s\", \"Players voted\": \"%s\"}", sessionName, Arrays.toString(cardValues), sessionID.toString(), playerVotes.size() + "/" + Players.size());
        }
        return String.format("{\"Session\": \"%s\", \"Cards\": %s, \"Last Vote\": %.1f, \"SessionID\": \"%s\", \"Players voted\": \"%s\"}", sessionName, Arrays.toString(cardValues), lastVote, sessionID.toString(), playerVotes.size() + "/" + Players.size());
    }

    public void addPlayer(String playerName) {
        Players.add(playerName);
    }

    public void vote(String playerName, int vote) {
        if (!Players.contains(playerName)) {
            throw new IllegalArgumentException("Player not in session");
        }
        playerVotes.put(playerName, vote);
    }

    public void finishVoting() {
        if(playerVotes.isEmpty()){
            return;
        }
        int sum = 0;
        for (int vote : playerVotes.values()) {
            sum += vote;
        }
        lastVote = (float) sum /playerVotes.size();
        playerVotes.clear();
    }

}
