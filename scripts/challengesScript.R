suppressMessages(library(caret))
args <- commandArgs(TRUE)
table <- paste("../../logger-data/challenges/challengesDataForR_", args[1], ".txt", sep="")
formula <- "result~"

for (i in seq_along(args)) {
    if (i > 1) {
        formula <- paste(formula, args[i], sep="+")
    }
}

formula <- as.formula(formula)

challengesData <- read.table(table, sep=",", header=TRUE)
challengesData$result <- as.factor(challengesData$result)

ctrl <- trainControl(method="repeatedcv", number=2, repeats=5)
mod_fit <- train(formula, data=challengesData, method="glm", family="binomial", trControl=ctrl)

mod_fit$results
summary(mod_fit)
warnings()