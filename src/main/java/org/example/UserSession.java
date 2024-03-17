package org.example;

import java.util.UUID;

public class UserSession {
    public String username;
    public UUID PokerSessionID;

    public UserSession(String username) {
        this.username = username;
    }

    public void setPokerSessionID(UUID pokerSessionID) {
        PokerSessionID = pokerSessionID;
    }

    public String getUsername() {
        return username;
    }

    public UUID getPokerSessionID() {
        return PokerSessionID;
    }

}
