package org.example;

import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;

import java.io.IOException;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.UUID;

public class PokerHandler implements HttpHandler {

    HashMap<UUID, PokerSession> pokerSessions = new HashMap<>();
    HashMap<UUID, UserSession> userSessions = new HashMap<>();
    @Override
    public void handle(HttpExchange exchange) throws IOException {
        String requestPath = exchange.getRequestURI().getPath();
        System.out.println(requestPath);
        //get request parameters
        LinkedHashMap<String, String> params = new LinkedHashMap<>();
        String query = exchange.getRequestURI().getQuery();
        if (query != null) {
            for (String param : query.split("&")) {
                String[] entry = param.split("=");
                if (entry.length > 1) {
                    params.put(entry[0], entry[1]);
                } else {
                    params.put(entry[0], "");
                }
            }
        }
        exchange.getResponseHeaders().add("Access-Control-Allow-Origin", "*");
        exchange.getResponseHeaders().add("Access-Control-Allow-Methods", "GET, OPTIONS, HEAD, PUT, POST");

        // Handle the OPTIONS method request specifically
        if (exchange.getRequestMethod().equalsIgnoreCase("OPTIONS")) {
            exchange.getResponseHeaders().add("Access-Control-Allow-Headers", "Content-Type,Authorization");
            exchange.sendResponseHeaders(204, -1);
            return;
        }
        System.out.println(params);
        try {
            switch (requestPath) {
                case "/poker":
                    login(exchange, params);
                    break;
                case "/poker/create":
                    createPokerSession(exchange, params);
                    break;
                case "/poker/join":
                    joinPokerSession(exchange, params);
                    break;
                case "/poker/getUpdates":
                    getUpdates(exchange, params);
                    break;
                case "/poker/submitVote":
                    submitVote(exchange, params);
                    break;
                case "/poker/finishVoting":
                    finishVoting(exchange, params);
                    break;
                default:
                    sendResponse(exchange, "Not found");
            }
        }catch (Exception e) {
            e.printStackTrace();
            sendResponse(exchange, "Error");
        }
    }

    private void finishVoting(HttpExchange exchange, LinkedHashMap<String, String> params) {
        if(!params.containsKey("userSessionID") || params.get("userSessionID").isEmpty()){
            sendResponse(exchange, "Session ID is required");
            return;
        }
        UUID userSessionID = UUID.fromString(params.get("userSessionID"));
        if(!userSessions.containsKey(userSessionID)){
            sendResponse(exchange, "Session not found");
            return;
        }
        UUID pokerSessionID = userSessions.get(userSessionID).getPokerSessionID();
        if(!pokerSessions.containsKey(pokerSessionID)){
            sendResponse(exchange, "Session not found");
            return;
        }
        pokerSessions.get(pokerSessionID).finishVoting();
        sendResponse(exchange, "Voting finished");
    }

    private void submitVote(HttpExchange exchange, LinkedHashMap<String, String> params) {
        if(!params.containsKey("userSessionID") || params.get("userSessionID").isEmpty() || !params.containsKey("vote") || params.get("vote").isEmpty()){
            sendResponse(exchange, "Session ID and vote are required");
            return;
        }
        UUID userSessionID = UUID.fromString(params.get("userSessionID"));
        if(!userSessions.containsKey(userSessionID)){
            sendResponse(exchange, "Session not found");
            return;
        }
        UUID pokerSessionID = userSessions.get(userSessionID).getPokerSessionID();
        if(!pokerSessions.containsKey(pokerSessionID)){
            sendResponse(exchange, "Session not found");
            return;
        }
        PokerSession pokerSession = pokerSessions.get(pokerSessionID);
        if(pokerSession == null){
            sendResponse(exchange, "Session not found");
            return;
        }
        pokerSession.vote(userSessions.get(userSessionID).getUsername(), Integer.parseInt(params.get("vote")));
        //pokerSessions.get(pokerSessionID).vote(userSessions.get(userSessionID).getUsername(), Integer.parseInt(params.get("vote")));
        sendResponse(exchange, "Vote submitted");
    }

    private void getUpdates(HttpExchange exchange, LinkedHashMap<String, String> params) {
        if(!params.containsKey("userSessionID") || params.get("userSessionID").isEmpty()){
            sendResponse(exchange, "Session ID is required");
            return;
        }
        UUID userSessionID = UUID.fromString(params.get("userSessionID"));
        if(!userSessions.containsKey(userSessionID)){
            sendResponse(exchange, "{\"error\":\"Session not found\"}");
            return;
        }
        UUID pokerSessionID = userSessions.get(userSessionID).getPokerSessionID();
        if(!pokerSessions.containsKey(pokerSessionID)){
            sendResponse(exchange, "{\"error\":\"Session not found\"}");
            return;
        }
        sendResponse(exchange, pokerSessions.get(pokerSessionID).getSessionDetails());
    }

    private void login(HttpExchange exchange, LinkedHashMap<String, String> params) {
        if (!params.containsKey("Username")){
            sendResponse(exchange, "{\"error\":\"Username is required\"}");
            return;
        }
        UUID sessionID = UUID.randomUUID();
        userSessions.put(sessionID, new UserSession(params.get("Username")));
        sendResponse(exchange, "{\"sessionID\":\"" + sessionID + "\"}");
    }

    private void joinPokerSession(HttpExchange exchange, LinkedHashMap<String, String> params) {
        if(!params.containsKey("sessionID") || params.get("sessionID").isEmpty() || !params.containsKey("userSession") || params.get("userSession").isEmpty()){
            sendResponse(exchange, "Session ID and username are required");
            return;
        }
        UUID sessionID = UUID.fromString(params.get("sessionID"));
        UUID userSession = UUID.fromString(params.get("userSession"));
        if(!pokerSessions.containsKey(sessionID)){
            sendResponse(exchange, "Session not found");
            System.out.println(pokerSessions);
            return;
        }
        if(!userSessions.containsKey(userSession)){
            sendResponse(exchange, "User session not found");
            return;
        }
        userSessions.get(userSession).setPokerSessionID(sessionID);
        pokerSessions.get(sessionID).addPlayer(userSessions.get(userSession).getUsername());
        sendResponse(exchange, "Joined session");
    }

    private void createPokerSession(HttpExchange exchange, LinkedHashMap<String, String> params) {
        if(!params.containsKey("cardValues") ||!params.containsKey("sessionName") || params.get("sessionName").isEmpty() || params.get("cardValues").isEmpty()){
            sendResponse(exchange, "Session name is required");
            return;
        }
        UUID sessionID = UUID.randomUUID();
        int[] cardValues = new int[params.get("cardValues").split(",").length];
        for (int i = 0; i < cardValues.length; i++) {
            cardValues[i] = Integer.parseInt(params.get("cardValues").split(",")[i]);
        }
        pokerSessions.put(sessionID, new PokerSession(params.get("sessionName"), cardValues, sessionID));
        sendResponse(exchange, "{\"sessionID\":\"" + sessionID + "\"}");
    }

    private void sendResponse(HttpExchange exchange, String response) {
        System.out.println(response);
        exchange.getResponseHeaders().set("Content-Type", "text/plain");
        try {
            exchange.sendResponseHeaders(200, response.length());
            exchange.getResponseBody().write(response.getBytes());
            exchange.getResponseBody().close();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
