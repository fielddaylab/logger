args <- commandArgs(TRUE)
table <- paste("challenges/challengesDataForR_", args[1], ".txt", sep="")
formula <- "result~"

for (i in seq_along(args)) {
    if (i > 1) {
        formula <- paste(formula, args[i], sep="+")
    }
}

formula <- as.formula(formula)

challengesData <- read.table(table, sep=",", header=TRUE)
fit <- glm(formula,data=challengesData,family=binomial(link="logit"))
summary(fit)